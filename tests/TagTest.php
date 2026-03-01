<?php

namespace alcamo\x690;

use PHPUnit\Framework\TestCase;
use alcamo\exception\SyntaxError;

class TagTest extends TestCase
{
    /**
     * @dataProvider basicsProvider
     */
    public function testBasics(
        $hexData,
        $expectedIsConstructed,
        $expectedIsEoC
    ): void {
        $tag = Tag::newFromHex($hexData);

        $this->assertSame($expectedIsConstructed, $tag->isConstructed());

        $this->assertSame($expectedIsEoC, $tag->isEoc());
    }

    public function basicsProvider(): array
    {
        return [
            [ '00', false, true ],
            [ '01', false, false ],
            [ 'E0', true, false ],
            [ 'DF01', false, false ],
            [ '9F8370', false, false ]
        ];
    }

    public function testConstructException1()
    {
        $this->expectException(SyntaxError::class);
        $this->expectExceptionMessage(
            'Syntax error in <alcamo\x690\Tag>"F000"; '
            . 'short form longer than 1 byte'
        );

        new Tag("\xF0\x00");
    }

    public function testConstructException2()
    {
        $this->expectException(SyntaxError::class);
        $this->expectExceptionMessage(
            'Syntax error in <alcamo\x690\Tag>"DF0000"; '
            . 'long form not correctly encoded'
        );

        new Tag("\xDF\x00\x00");
    }

    public function testConstructException3()
    {
        $this->expectException(SyntaxError::class);
        $this->expectExceptionMessage(
            'Syntax error in <alcamo\x690\Tag>"DF8087"; '
            . 'long form not correctly encoded'
        );

        new Tag("\xDF\x80\x87");
    }

    public function testConstructException4()
    {
        $this->expectException(SyntaxError::class);
        $this->expectExceptionMessage(
            'Syntax error in <alcamo\x690\Tag>"DF8000"; '
            . 'long form not correctly encoded'
        );

        new Tag("\xDF\x80\x00");
    }

    public function testConstructException5()
    {
        $this->expectException(SyntaxError::class);
        $this->expectExceptionMessage(
            'Syntax error in <alcamo\x690\Tag>"DF"; long form only 1 byte long'
        );

        new Tag("\xDF");
    }
}
