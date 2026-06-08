<?php

namespace alcamo\x690;

use alcamo\input_stream\StringInputStream;
use PHPUnit\Framework\TestCase;

class HtmlTlvDumperTest extends TestCase
{
    /**
     * @dataProvider dumpTlvDeInstanceProvider
     */
    public function testDumpTlvDeInstance(
        $flags,
        $htmlParams,
        $hexData,
        $expectedDump
    ): void {
        $tlvDumper = new HtmlTlvDumper(null, $flags, $htmlParams);

        $tlvDeserializer = new TlvDeserializer();

        $deInstance = $tlvDeserializer->deserializeTlvDeInstance(
            new StringInputStream(hex2bin($hexData))
        );

        $dump = $tlvDumper->dumpTlvDeInstance($deInstance);

        $this->assertSame($expectedDump, $dump);
    }

    public function dumpTlvDeInstanceProvider(): array
    {
        return [
            [
                0,
                null,
                '0000',
                "<span title=\"End-of-Content (b 0)\">00</span> &lt; '' &gt;"
            ],
            [
                TlvDumper::DUMP_COMMENTS | TlvDumper::DUMP_LENGTH,
                [
                    'commentClass' => 'comment',
                    'lengthClass'   => 'length',
                    'literalClass'  => 'literal'
                ],
                '0C03426172',
                "<span class=\"comment\">; UTF8String (ans)</span>\n"
                . "0C <span class=\"length\" title=\"decimal 3\">[3]</span> "
                    . "&lt; <span class=\"literal\">\"Bar\"</span> &gt;"
            ],
            [
                0,
                [
                    'tagClass' => 'tag',
                    'tagHrefPrefix' => 'https://tags.example.com/'
                ],
                '3F3105' . '030306F0F0',
                "<a class=\"tag\" title=\"Set (b)\" "
                . "href=\"https://tags.example.com/3F31\">3F31</a> &lt; "
                . "<a class=\"tag\" title=\"Bitstring (b)\" "
                . "href=\"https://tags.example.com/03\">03</a> &lt; "
                . "\"1111000011\" &gt; &gt;"
            ]
        ];
    }
}
