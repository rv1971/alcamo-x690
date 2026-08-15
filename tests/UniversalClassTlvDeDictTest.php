<?php

namespace alcamo\x690;

use PHPUnit\Framework\TestCase;
use alcamo\rdf_literal\{
    BitStringLiteral,
    BooleanLiteral,
    ConstructedHexBinaryLiteral,
    HexBinaryLiteral,
    IntegerLiteral
};

require_once(__DIR__ . DIRECTORY_SEPARATOR . 'MyTlvDeDict.php');

class UniversalClassTlvDeDictTest extends TestCase
{
    public const XSD_NS = AbstractTlvDataElement::XSD_NS;

    /**
     * @dataProvider lookupProvider
     */
    public function testLookup(
        $hexTag,
        $expectedLabel
    ): void {
        $tag = Tag::newFromHex($hexTag);

        $dict = new UniversalClassTlvDeDict();

        $dataElement = $dict->lookup($tag);

        $this->assertSame($dataElement, $dict->lookup($tag, 'foo'));

        $this->assertSame($expectedLabel, $dataElement->getLabel());
    }

    public function lookupProvider(): array
    {
        return [
            [ '00', 'End-of-Content (b 0)' ],
            [ '01', 'Boolean (b 1)' ],
            [ '02', 'Integer (b)' ],
            [ '03', 'Bitstring (b)' ],
            [ '04', 'Octetstring (b)' ],
            [ '05', 'Null (b 0)' ],
            [ '06', 'Object identifier (ans)' ],
            [ '07', 'Object descriptor (ans)' ],
            [ '08', 'Unknown (b)' ],
            [ '0A', 'Enumerated (b)' ],
            [ '0C', 'UTF8String (ans)' ],
            [ '0D', 'Relative object identifier (ans)' ],
            [ '12', 'NumericString (ans)' ],
            [ '13', 'PrintableString (ans)' ],
            [ '17', 'UTCTime (an 13)' ],
            [ '18', 'GeneralizedTime (an 15)' ],
            [ '19', 'GraphicString (ans)' ],
            [ '1A', 'VisibleString (ans)' ],
            [ '1B', 'GeneralString (ans)' ],
            [ '1C', 'UniversalString (ans)' ],
            [ '1E', 'BMPString (ans)' ],
            [ '1F1F', 'DATE (an 8)' ],
            [ '1F20', 'TIME-OF-DAY (an 6)' ],
            [ '1F21', 'DATE-TIME (an 14)' ],
            [ '1F23', 'OID internationalized resource identifier (ans)' ],
            [ '1F24', 'Relative OID internationalized resource identifier (ans)' ],
            [ '3F28', 'External (b)' ],
            [ '3F2B', 'Embedded-pdv (b)' ],
            [ '3F30', 'Sequence (b)' ],
            [ '3F31', 'Set (b)' ],
            [ '1F32', 'Unknown (b)' ]
        ];
    }

    public function testGetEocDeInstance(): void
    {
        $dict = new UniversalClassTlvDeDict();

        $eocDeInstance = $dict->getEocDeInstance();

        $this->assertSame(
            $dict->lookup(Tag::newFromHex('00'), 'bar'),
            $eocDeInstance->getDataElement()
        );

        $this->assertTrue(
            (new HexBinaryLiteral(''))->equals($eocDeInstance->getLiteral())
        );

        $this->assertSame($eocDeInstance, $dict->getEocDeInstance());
    }

    /**
     * @dataProvider createPrimitiveTlvDeInstanceProvider
     */
    public function testCreatePrimitiveTlvDeInstance(
        $value,
        $hexTag,
        $expectedLiteral,
        $expectedHexSerialization
    ): void {
        $dict = new MyTlvDeDict();

        $serializer = new TlvSerializer();

        $tlvDeInstance = $dict->createPrimitiveTlvDeInstance($value, $hexTag);

        $this->assertSame(
            get_class($expectedLiteral),
            get_class($tlvDeInstance->getLiteral())
        );

        $this->assertTrue(
            $expectedLiteral->equals($tlvDeInstance->getLiteral())
        );

        $this->assertSame(
            $expectedHexSerialization,
            bin2hex($serializer->serializeTlvDeInstance($tlvDeInstance))
        );
    }

    public function createPrimitiveTlvDeInstanceProvider(): array
    {
        return [
            [ true, '01', new BooleanLiteral(true), '010101' ],
            [ '1010', '03', new BitStringLiteral('1010'), '030204a0' ],
            [
                [ 43, 44 ],
                'C0',
                new ConstructedHexBinaryLiteral(
                    [
                        new IntegerLiteral(43),
                        new IntegerLiteral(44)
                    ]
                ),
                'c0022b2c'
            ]
        ];
    }
}
