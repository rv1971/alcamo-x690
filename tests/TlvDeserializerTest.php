<?php

namespace alcamo\x690;

use PHPUnit\Framework\TestCase;
use alcamo\data_element\{
    AbstractSerializer,
    DataElement,
    IntegerSerializer
};
use alcamo\exception\Eof;
use alcamo\rdf_literal\{
    BooleanLiteral,
    HexBinaryLiteral,
    IntegerLiteral,
    StringLiteral
};
use alcamo\input_stream\StringInputStream;

class MyTlvDataElementDict extends UniversalClassTlvDataElementDict
{
    public function lookup($tag, $parent = null): TlvDataElementInterface
    {
        if ($tag == '9F00' && $parent == '3F30') {
            return new PrimitiveTlvDataElement(
                Tag::newFromHex($tag),
                new DataElement(
                    $this->literalWorkbench_->getSchema()
                        ->getGlobalType(self::XSD_NS . ' short')
                ),
                IntegerSerializer::newFromProps(
                    [
                        'datatypeXName' => self::XSD_NS . ' short',
                        'encoding' => 'BIG-ENDIAN'
                    ]
                )
            );
        }

        return parent::lookup($tag, $parent);
    }
}

class TlvDeSerializerTest extends TestCase
{
    public const XSD_NS = AbstractTlvDataElement::XSD_NS;

    public function testDeserializeTag(): void
    {
        $tlvDeSerializer = new TlvDeserializer();

        $istream = new StringInputStream(hex2bin('00011f1fdf8522'));

        $this->assertEquals(
            Tag::newFromHex('00'),
            $tlvDeSerializer->deserializeTag($istream)
        );

        $this->assertEquals(
            Tag::newFromHex('01'),
            $tlvDeSerializer->deserializeTag($istream)
        );

        $this->assertEquals(
            Tag::newFromHex('1f1f'),
            $tlvDeSerializer->deserializeTag($istream)
        );

        $this->assertEquals(
            Tag::newFromHex('df8522'),
            $tlvDeSerializer->deserializeTag($istream)
        );

        $this->expectException(Eof::class);

        $this->expectExceptionMessage(
            'Failed to read 1 unit(s) from object '
                . '<alcamo\binary_data\BinaryString>"00011F1FDF8522", '
                . 'only 0 unit(s) available at offset 7'
        );

        $tlvDeSerializer->deserializeTag($istream);
    }

    public function testDeserializeTagExcpetion(): void
    {
        $tlvDeSerializer = new TlvDeserializer();

        $istream = new StringInputStream(hex2bin('3f'));

        $this->expectException(Eof::class);

        $this->expectExceptionMessage(
            'Failed to read 1 unit(s) from object '
            . '<alcamo\binary_data\BinaryString>"3F", '
            . 'only 0 unit(s) available at offset 1'
        );

        $tlvDeSerializer->deserializeTag($istream);
    }

    public function testDeserializeLength(): void
    {
        $tlvDeSerializer = new TlvDeserializer();

        $istream = new StringInputStream(hex2bin('00801282010f'));

        $this->assertEquals(0, $tlvDeSerializer->deserializeLength($istream));

        $this->assertEquals(-1, $tlvDeSerializer->deserializeLength($istream));

        $this->assertEquals(18, $tlvDeSerializer->deserializeLength($istream));

        $this->assertEquals(271, $tlvDeSerializer->deserializeLength($istream));

        $this->expectException(Eof::class);

        $this->expectExceptionMessage(
            'Failed to read 1 unit(s) from object '
            . '<alcamo\binary_data\BinaryString>"00801282010F", '
            . 'only 0 unit(s) available at offset 6'
        );

        $tlvDeSerializer->deserializeLength($istream);
    }

    public function testDeserializeLengthExcpetion(): void
    {
        $tlvDeSerializer = new TlvDeserializer();

        $istream = new StringInputStream(hex2bin('831234'));

        $this->expectException(Eof::class);

        $this->expectExceptionMessage(
            'Failed to read 3 unit(s) from stream '
            . '<alcamo\binary_data\BinaryString>"831234", '
            . 'only 2 unit(s) available at offset 1'
        );

        $tlvDeSerializer->deserializeLength($istream);
    }

    /**
     * @dataProvider deserializeTlvDeInstanceProvider
     */
    public function testDeserializeTlvDeInstance(
        $hexData,
        $expectedDeInstance
    ): void {
        $tlvDeserializer = new TlvDeserializer(new MyTlvDataElementDict());

        $tlvSerializer = new TlvSerializer();

        $binData = hex2bin($hexData);

        $istream = new StringInputStream($binData);

        $deInstance = $tlvDeserializer->deserializeTlvDeInstance($istream);

        $this->assertFalse($istream->isGood());

        if ($expectedDeInstance->getDataElement()->getTag()->isConstructed()) {
            $this->assertEquals(
                $expectedDeInstance->getDataElement(),
                $deInstance->getDataElement()
            );

            $expectedChildren = $expectedDeInstance->getChildren();

            $this->assertSame(
                count($expectedChildren),
                count($deInstance->getChildren())
            );

            reset($expectedChildren);

            foreach ($deInstance->getChildren() as $child) {
                $this->assertEquals(
                    current($expectedChildren)->getDataElement(),
                    $child->getDataElement()
                );

                $this->assertTrue(
                    $child->getLiteral()
                        ->equals(current($expectedChildren)->getLiteral())
                );

                next($expectedChildren);
            }

        } else {
            $this->assertEquals(
                $expectedDeInstance->getDataElement(),
                $deInstance->getDataElement()
            );

            $this->assertTrue(
                $deInstance->getLiteral()
                    ->equals($expectedDeInstance->getLiteral())
            );
        }

        $this->assertSame(
            $binData,
            $tlvSerializer->serializeTlvDeInstance($deInstance)
        );
    }

    public function deserializeTlvDeInstanceProvider(): array
    {
        $tlvDeDict = new MyTlvDataElementDict();

        return [
            [ '0000', $tlvDeDict->getEocDeInstance() ],
            [
                '0101ff',
                PrimitiveTlvDeInstance::newFromLiteral(
                    $tlvDeDict->lookup('01'),
                    new BooleanLiteral(true)
                )
            ],
            [
                '0C03466f6f',
                PrimitiveTlvDeInstance::newFromLiteral(
                    $tlvDeDict->lookup('0C'),
                    new StringLiteral('Foo')
                )
            ],
            [
                '3F310D' . '0401ff' . '0500' . '12023432' . '9F0001AB',
                ConstructedTlvDeInstance::newFromChildren(
                    $tlvDeDict->lookup('3F31'),
                    [
                        PrimitiveTlvDeInstance::newFromLiteral(
                            $tlvDeDict->lookup('04'),
                            new HexBinaryLiteral('ff')
                        ),
                        PrimitiveTlvDeInstance::newFromLiteral(
                            $tlvDeDict->lookup('05'),
                            new HexBinaryLiteral('')
                        ),
                        PrimitiveTlvDeInstance::newFromLiteral(
                            $tlvDeDict->lookup('12'),
                            new StringLiteral('42')
                        ),
                        PrimitiveTlvDeInstance::newFromLiteral(
                            $tlvDeDict->lookup('9F00'),
                            new HexBinaryLiteral('AB')
                        )
                    ]
                )
            ],
            [
                '3F3005' . '9F0002002A',
                ConstructedTlvDeInstance::newFromChildren(
                    $tlvDeDict->lookup('3F30'),
                    [
                        PrimitiveTlvDeInstance::newFromLiteral(
                            $tlvDeDict->lookup('9F00', '3F30'),
                            new IntegerLiteral(42, self::XSD_NS . '#short')
                        )
                    ]
                )
            ]
        ];
    }
}
