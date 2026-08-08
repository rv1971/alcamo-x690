<?php

namespace alcamo\x690;

use alcamo\data_element\{ConstructedDataElement, DataElement};
use alcamo\input_stream\StringInputStream;
use alcamo\rdf_literal_serializer\{ConstructedSerializer, IntegerSerializer};
use PHPUnit\Framework\TestCase;

class MyTlvDeDict extends UniversalClassTlvDeDict
{
    private $tlvDataElement_;

    public function __construct(?TlvDeWorkbench $tlvDeWorkbench = null)
    {
        parent::__construct($tlvDeWorkbench);

        $integerType = $this->tlvDeWorkbench_->getSchema()
            ->getGlobalType(UniversalClassTlvDeDict::XSD_NS . ' integer');

        $dataElement1 = new DataElement(
            $integerType,
            [ [ 'rdfs:label', 'Foo' ] ]
        );

        $dataElement2 = new DataElement(
            $integerType,
            [ [ 'rdfs:label', 'Bar' ] ]
        );

        $serializer = new IntegerSerializer(
            $integerType->getXName(),
            'BIG-ENDIAN',
            [ 1, 1 ]
        );

        $this->tlvDataElement_ = new PrimitiveTlvDataElement(
            Tag::newFromHex('C0'),
            new ConstructedDataElement(
                [ $dataElement1, $dataElement2 ],
                null,
                [ [ 'rdfs:label', 'Foo/Bar' ] ]
            ),
            ConstructedSerializer::newFromProps(
                [
                    'serializers' => [ $serializer, $serializer ],
                    'lengthRange' => [ 2, 2 ]
                ]
            )
        );
    }

    public function lookup($tag, $parent = null): TlvDataElementInterface
    {
        if ($tag == 'C0') {
            return $this->tlvDataElement_;
        }

        return parent::lookup($tag, $parent);
    }
}

class TlvDumperTest extends TestCase
{
    /**
     * @dataProvider dumpTlvDeInstanceProvider
     */
    public function testDumpTlvDeInstance(
        $flags,
        $hexData,
        $expectedDump
    ): void {
        $tlvDumper = new TlvDumper(null, $flags);

        $tlvDeserializer = new TlvDeserializer(new MyTlvDeDict());

        $deInstance = $tlvDeserializer->deserializeTlvDeInstance(
            new StringInputStream(hex2bin($hexData))
        );

        $dump = $tlvDumper->dumpTlvDeInstance($deInstance);

        $this->assertSame($expectedDump, $dump);
    }

    public function dumpTlvDeInstanceProvider(): array
    {
        return [
            [ 0, '0000', "00 < '' >" ],
            [
                TlvDumper::DUMP_LENGTH,
                '021000000000000000000000000000000102',
                "02 [10] < '0102' >"
            ],
            [
                TlvDumper::DUMP_COMMENTS,
                '0C03426172',
                "; UTF8String (ans)\n0C < \"Bar\" >"
            ],
            [
                TlvDumper::DUMP_LENGTH,
                '3F3105' . '030306F0F0',
                "3F31 [5] < 03 [3] < \"1111000011\" > >"
            ],
            [
                TlvDumper::DUMP_LENGTH,
                '3F3007' . '010101' . '0402abcd',
                "3F30 [7] <\n  01 [1] < '01' >\n  04 [2] < 'ABCD' >\n>"
            ],
            [
                TlvDumper::DUMP_COMMENTS | TlvDumper::DUMP_LENGTH,
                '0500',
                "; Null (b 0)\n05 [0] < '' >"
            ],
            [
                TlvDumper::DUMP_COMMENTS,
                'A106' . '120431323334',
                "; Unknown (b)\n"
                . "A1 <\n"
                . "  ; NumericString (ans)\n"
                . "  12 < \"1234\" >\n"
                . ">"
            ],
            [
                TlvDumper::DUMP_COMMENTS,
                '3F3015'
                . 'A211' . '180F32303236303532373137313534325a'
                . '1B00',
                "; Sequence (b)\n"
                . "3F30 <\n"
                . "  ; Unknown (b)\n"
                . "  A2 <\n"
                . "    ; GeneralizedTime (an 15)\n"
                . "    18 < \"20260527171542Z\" >\n"
                . "  >\n"
                . "  ; GeneralString (ans)\n"
                . "  1B < \"\" >\n"
                . ">"
            ],
            [
                TlvDumper::DUMP_COMMENTS,
                'C0023A3B',
                "; Foo/Bar (b 2)\n"
                . ";  1. Foo\n"
                . ";  2. Bar\n"
                . "C0 < [ '3A' '3B' ] >"
            ],

        ];
    }
}
