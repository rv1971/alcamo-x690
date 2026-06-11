<?php

namespace alcamo\x690;

use alcamo\exception\SyntaxError;
use alcamo\input_stream\StringInputStream;
use alcamo\rdf_literal\{
    BooleanLiteral,
    HexBinaryLiteral,
    IntegerLiteral,
    StringLiteral
};
use PHPUnit\Framework\TestCase;

class TlvDedumperTest extends TestCase
{
    /**
     * @dataProvider dedumpTlvDeInstanceProvider
     */
    public function testDedumpTlvDeInstance(
        $dumpText,
        $expectedDeInstance
    ): void {
        $tlvDedumper = new TlvDedumper();

        $deInstance = $tlvDedumper->dedumpTlvDeInstance(
            new StringInputStream($dumpText)
        );

        if (!$deInstance->equals($expectedDeInstance)) {
            echo "*** expected {$expectedDeInstance->getRawContent()}\n";
            echo "*** found {$deInstance->getRawContent()}\n";
        }

        $this->assertTrue($deInstance->equals($expectedDeInstance));
    }

    public function dedumpTlvDeInstanceProvider(): array
    {
        $tlvDeDict = new UniversalClassTlvDataElementDict();

        return [
            [
                "; EoC\n00 <>",
                PrimitiveTlvDeInstance::newFromLiteral(
                    $tlvDeDict->lookup('00'),
                    new HexBinaryLiteral('')
                )
            ],
            [
                "3F31 < 02 < '2A' > >",
                ConstructedTlvDeInstance::newFromChildren(
                    $tlvDeDict->lookup('3F31'),
                    [
                        PrimitiveTlvDeInstance::newFromLiteral(
                            $tlvDeDict->lookup('02'),
                            new IntegerLiteral(42)
                        )
                    ]
                )
            ],
            [
                "; blabla\n; blablabla\n"
                    . "3F30 [40] <\n"
                    . "; Lorem ipsum dolor sit amet\n"
                    . "01 < '01' >\n"
                    . "; consetetur sadipscing elitr,\n"
                    . "; sed diam nonumy eirmod tempor invidunt\n"
                    . "; ut labore et dolore magna aliquyam erat,\n"
                    . "; sed diam voluptua.\n"
                    . "02 [3] <'11'>\n"
                    . "0C<\"foo\" >\n"
                    . "0C < '626172' > 3F31 <>\n"
                    . "\t>",
                ConstructedTlvDeInstance::newFromChildren(
                    $tlvDeDict->lookup('3F30'),
                    [
                        PrimitiveTlvDeInstance::newFromLiteral(
                            $tlvDeDict->lookup('01'),
                            new BooleanLiteral(true)
                        ),
                        PrimitiveTlvDeInstance::newFromLiteral(
                            $tlvDeDict->lookup('02'),
                            new IntegerLiteral(17),
                            3
                        ),
                        PrimitiveTlvDeInstance::newFromLiteral(
                            $tlvDeDict->lookup('0C'),
                            new StringLiteral("foo")
                        ),
                        PrimitiveTlvDeInstance::newFromLiteral(
                            $tlvDeDict->lookup('0C'),
                            new StringLiteral("bar")
                        ),
                        ConstructedTlvDeInstance::newFromChildren(
                            $tlvDeDict->lookup('3F31'),
                            []
                        )
                    ],
                    64
                )
            ]
        ];
    }

    public function testException(): void
    {
        $tlvDedumper = new TlvDedumper();

        $istream = new StringInputStream('01 x');

        $this->expectException(SyntaxError::class);

        $this->expectExceptionMessage(
            'Syntax error, expected one of "<" in "01 x" at offset 3 ("x") '
                . "in tag '01'"
        );

        $tlvDedumper->dedumpTlvDeInstance($istream);
    }
}
