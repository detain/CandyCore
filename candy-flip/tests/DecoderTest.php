<?php

declare(strict_types=1);

namespace SugarCraft\Flip\Tests;

use SugarCraft\Flip\Decoder;
use PHPUnit\Framework\TestCase;

final class DecoderTest extends TestCase
{
    private string $gifPath;

    protected function setUp(): void
    {
        // Minimal 1x1 red pixel GIF87a
        $this->gifPath = sys_get_temp_dir() . '/test-' . uniqid() . '.gif';
        // GIF87a header: 6 bytes
        // Logical Screen Descriptor: 7 bytes
        // Global Color Table (3 colors = 9 bytes)
        // Image Descriptor: 10 bytes
        // LZW min code size: 1 byte
        // Image data (0x21 = GIF trailer): 2 bytes
        $gif = "\x47\x49\x46\x38\x37\x61"  // GIF87a header
             . "\x01\x00\x01\x00\x80\x00\x00" // LSD: 1x1, global color table, bg=0, par=0
             . "\x00\x00\x00"                  // GCT: color 0 = black
             . "\xff\x00\x00\x00\x00\x00\x00" // color 1 = red, color 2 = black
             . "\x2c\x00\x00\x00\x00\x01\x00\x01\x00\x00" // Image Descriptor
             . "\x02"                          // LZW min code size
             . "\x44\x00\x3b";                 // image data + GIF trailer
        file_put_contents($this->gifPath, $gif);
    }

    protected function tearDown(): void
    {
        if (isset($this->gifPath) && file_exists($this->gifPath)) {
            unlink($this->gifPath);
        }
    }

    public function testDecodeReturnsFrames(): void
    {
        if (!extension_loaded('gd')) {
            $this->markTestSkipped('ext-gd not available');
        }
        $frames = Decoder::decode($this->gifPath, 1, 1);
        $this->assertNotEmpty($frames);
    }

    public function testDecodeReturnsCorrectDimensions(): void
    {
        if (!extension_loaded('gd')) {
            $this->markTestSkipped('ext-gd not available');
        }
        $frames = Decoder::decode($this->gifPath, 2, 2);
        $this->assertInstanceOf(\SugarCraft\Flip\Frame::class, $frames[0]);
        $this->assertSame(2, $frames[0]->width());
        $this->assertSame(2, $frames[0]->height());
    }

    public function testDecodeThrowsForNonexistentFile(): void
    {
        $this->expectException(\RuntimeException::class);
        Decoder::decode('/nonexistent/path/file.gif', 1, 1);
    }

    public function testDecodeThrowsForInvalidBytes(): void
    {
        $badPath = sys_get_temp_dir() . '/not-a-gif-' . uniqid();
        file_put_contents($badPath, 'NOT A GIF FILE');
        try {
            $this->expectException(\RuntimeException::class);
            Decoder::decode($badPath, 1, 1);
        } finally {
            unlink($badPath);
        }
    }

    public function testDecodeThrowsForTooShortBytes(): void
    {
        $badPath = sys_get_temp_dir() . '/short-' . uniqid();
        file_put_contents($badPath, 'GIF');
        try {
            $this->expectException(\RuntimeException::class);
            Decoder::decode($badPath, 1, 1);
        } finally {
            unlink($badPath);
        }
    }
}
