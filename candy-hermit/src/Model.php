<?php

declare(strict_types=1);

namespace CandyCore\Hermit;

/**
 * Model interface for embedding Hermit in a Bubble-Tea-style application.
 *
 * Implement this to create a component that holds a Hermit and responds
 * to messages (keystrokes) by updating its state.
 */
interface Model
{
    /**
     * Handle a message and return the updated model.
     *
     * @param Hermit $hermit  The current Hermit state
     * @param string $msg     A key event string (e.g. "enter", "ctrl+c", "backspace")
     * @return Model  A new model instance (may be $this for same-state)
     */
    public function update(Hermit $hermit, string $msg): Model;

    /**
     * Render the view given the current Hermit state.
     *
     * @param Hermit $hermit  The current Hermit state
     * @return string  The rendered view string
     */
    public function view(Hermit $hermit): string;
}
