<?php

declare(strict_types=1);

namespace SugarCraft\Crush\Messages;

use SugarCraft\Crush\Tools\ToolCall;

/**
 * Strips and heals the message shapes every provider converter rejects,
 * immediately before a send.
 *
 * prompt_plan.md P9.S7 ("History sanitisation before every send"). The wire
 * in is {@see \SugarCraft\Crush\Runtime::buildMessages()} — the last function
 * every request passes before its CompleteRequest is built, so the rule holds
 * for every provider without any provider owning it. Vertex already dropped
 * empty assistant turns for itself; this makes the drop universal and
 * pre-provider instead.
 *
 * WHY THIS CLASS EXISTS, in the shapes that produce the bad wire:
 *  - The refusal path (Chat.php :2498-2509) commits
 *    `Message::assistant('')` carrying the denied call's result, and
 *    {@see \SugarCraft\Crush\Backend\EngineBackend::toTypedMessages()} maps
 *    that to an empty typed AssistantMessage. An empty AssistantMessage with
 *    no tool calls serializes to a BARE `{"role":"assistant"}` row, because
 *    the OpenAI-shape converters drop the empty `content` key with
 *    `array_filter` ({@see \SugarCraft\Crush\Providers\SglangProvider::formatMessages()}
 *    and {@see \SugarCraft\Crush\Providers\OpenAIProvider::formatMessages()}).
 *    Servers in that family answer 400; SglangProviderTest pins the bare row.
 *  - The ESC-ESC cancel (Chat.php :1571-1578) appends only the
 *    `_Request cancelled._` notice and leaves whatever partial rows existed
 *    behind them.
 *
 * §1.12 HONESTY — which operations carry live value on which path. Operation
 * (4), dropping empty assistant rows, is the LIVE-value operation: the
 * refusal shape above reaches a provider today. Operations (2) and (3), the
 * orphan-result drop and the unanswered-call synthesis, are disclosed
 * defense-in-depth: on the Chat path the structure is lost upstream —
 * toTypedMessages strips tool calls and results before they could arrive
 * mismatched — and every engine-loop producer pairs correctly (the settle
 * echo reuses the original id, Runtime.php :2048-2054, and every failure
 * branch returns a paired result, Runtime.php :2402). They are
 * dormant-not-dead, in the E31 doctrine's words: a send is exactly where
 * these invariants must hold even if every producer today pairs correctly,
 * and a checkpoint revival or a future wire path that keeps the structure
 * makes them load-bearing without warning.
 *
 * Operations run in ONE pass over the list, in this order:
 *  (1) collect every call id from every AssistantMessage's toolCalls;
 *  (2) drop a ToolResultMessage whose toolCallId matches no collected call;
 *  (3) synthesize, immediately after an assistant row, an error result under
 *      the same id for each of its calls no row anywhere answers;
 *  (4) drop an AssistantMessage with empty content and no tool calls.
 *
 * The synthesized marker text is deliberately value-equal to, not imported
 * from, {@see \SugarCraft\Crush\Chat::INTERRUPTED_TOOL_CALL} — the repair
 * precedent this mirrors, {@see \SugarCraft\Crush\Chat::reviveCheckpointMessage()},
 * synthesizes the same pair after a crashed process. src carries no cross-file
 * coupling here; the tie is pinned by reflection in the test, so drift in
 * either constant goes red instead of silently diverging.
 *
 * Everything the pass does not recognize — SystemMessage, UserMessage, any
 * future Message type — passes through untouched, and the input array is
 * never mutated: a fresh list comes out, in the original order.
 */
final class HistorySanitizer
{
    /**
     * Error text of a synthesized stand-in result. MUST stay value-equal to
     * {@see \SugarCraft\Crush\Chat::INTERRUPTED_TOOL_CALL}; a test reads that
     * constant by reflection and asserts equality so the tie cannot rot.
     */
    private const INTERRUPTED_MARKER = 'Tool call interrupted by restart';

    /**
     * @param array<array-key, Message> $messages typed history about to be sent
     *
     * @return list<Message> the same messages, minus rows no converter can send
     */
    public static function sanitize(array $messages): array
    {
        $callIds = [];
        $answeredIds = [];

        foreach ($messages as $msg) {
            if ($msg instanceof AssistantMessage) {
                foreach ($msg->toolCalls() ?? [] as $call) {
                    $id = self::callId($call);
                    if ($id !== null) {
                        $callIds[$id] = true;
                    }
                }
            } elseif ($msg instanceof ToolResultMessage) {
                $answeredIds[$msg->toolCallId()] = true;
            }
        }

        $out = [];

        foreach ($messages as $msg) {
            if ($msg instanceof ToolResultMessage) {
                // (2) An answer to a call nobody made is not an answer.
                if (!isset($callIds[$msg->toolCallId()])) {
                    continue;
                }

                $out[] = $msg;

                continue;
            }

            if ($msg instanceof AssistantMessage) {
                $calls = $msg->toolCalls() ?? [];

                if ($calls === [] && $msg->content() === '') {
                    // (4) The bare `{"role":"assistant"}` row.
                    continue;
                }

                $out[] = $msg;

                foreach ($calls as $call) {
                    // (3) A call nobody answers, anywhere in the list.
                    $id = self::callId($call);
                    if ($id !== null && !isset($answeredIds[$id])) {
                        $out[] = new ToolResultMessage(
                            $id,
                            self::INTERRUPTED_MARKER,
                            isError: true,
                        );
                    }
                }

                continue;
            }

            $out[] = $msg;
        }

        return $out;
    }

    /**
     * The id a toolCalls entry will reach the wire as, or null when the entry
     * is shaped so no converter can name it. Tools\ToolCall objects are the
     * production shape ({@see \SugarCraft\Crush\Runtime}); the array spellings
     * mirror what the converters themselves accept —
     * {@see \SugarCraft\Crush\Providers\Concerns\ToolSchema::formatToolCalls()}
     * passes pre-shaped entries through untouched, so their `id` key is what
     * lands on the wire and must be what pairing matches against here.
     */
    private static function callId(mixed $call): ?string
    {
        if ($call instanceof ToolCall) {
            return $call->id();
        }

        if (is_array($call) && is_string($call['id'] ?? null) && $call['id'] !== '') {
            return $call['id'];
        }

        return null;
    }
}
