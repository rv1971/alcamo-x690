<?php

namespace alcamo\x690;

use PHPUnit\Framework\TestCase;
use alcamo\binary_data\ImmutableBinaryString;
use alcamo\data_element\{
    AbstractSerializer,
    BinarySerializer,
    DataElement,
    IntegerSerializer,
    NonNegativeIntegerSerializer,
    StringSerializer
};
use alcamo\dom\schema\SchemaFactory;
use alcamo\rdf_literal\{
    HexBinaryLiteral,
    NonNegativeIntegerLiteral,
    StringLiteral
};

class PrimitiveTlvDeInstanceTest extends TestCase
{
    public const XSD_NS = PrimitiveTlvDataElement::XSD_NS;

    private static $schema_;

    public static function setUpBeforeClass(): void
    {
        self::$schema_ = (new SchemaFactory())->getMainSchema();
    }

    /**
     * @dataProvider newFromLiteralProvider
     */
    public function testNewFromLiteral(
        $tagString,
        $typeXName,
        $serializerProps,
        $literal,
        $length,
        $expectedRawContent
    ) {
        $tlvDe = new PrimitiveTlvDataElement(
            Tag::newFromHex($tagString),
            new DataElement(self::$schema_->getGlobalType($typeXName)),
            AbstractSerializer::createFromProps($serializerProps),
        );

        $tlvDeInstance =
            PrimitiveTlvDeInstance::newFromLiteral($tlvDe, $literal, $length);

        $this->assertSame($tlvDe, $tlvDeInstance->getDataElement());

        $this->assertSame($literal, $tlvDeInstance->getLiteral());

        $expectedRawContent = new ImmutableBinaryString($expectedRawContent);

        $this->assertEquals(
            $expectedRawContent,
            $tlvDeInstance->getRawContent()
        );

        $this->assertSame(
            count($expectedRawContent),
            $tlvDeInstance->getLength()
        );
    }

    public function newFromLiteralProvider(): array
    {
        return [
            [
                'DF0A',
                self::XSD_NS . ' string',
                [
                    'class' => StringSerializer::class,
                    'lengthRange' => [ 1, 16 ]
                ],
                new StringLiteral('Foo'),
                null,
                'Foo'
            ],
            [
                '84',
                self::XSD_NS . ' hexBinary',
                [
                    'class' => BinarySerializer::class,
                    'lengthRange' => [ 5, 16 ]
                ],
                new HexBinaryLiteral('ABCD'),
                null,
                "\xAB\xCD\x00\x00\x00",
            ],
            [
                '9F02',
                self::XSD_NS . ' unsignedInt',
                [
                    'class' => NonNegativeIntegerSerializer::class,
                    'encoding' => 'BCD',
                    'lengthRange' => [ 1, 12 ]
                ],
                new NonNegativeIntegerLiteral(4242),
                5,
                "\x00\x42\x42"
            ]
        ];
    }

    /**
     * @dataProvider newFromRawContentProvider
     */
    public function testNewFromRawContent(
        $tagString,
        $typeXName,
        $serializerProps,
        $rawContent,
        $expectedLiteral
    ) {
        $tlvDe = new PrimitiveTlvDataElement(
            new Tag(hex2bin($tagString)),
            new DataElement(self::$schema_->getGlobalType($typeXName)),
            AbstractSerializer::createFromProps($serializerProps),
        );

        $rawContent = new ImmutableBinaryString($rawContent);

        $tlvDeInstance = PrimitiveTlvDeInstance::newFromRawContent(
            $tlvDe,
            $rawContent
        );

        $this->assertSame($tlvDe, $tlvDeInstance->getDataElement());

        $this->assertSame($rawContent, $tlvDeInstance->getRawContent());

        $this->assertTrue(
            $expectedLiteral->equals($tlvDeInstance->getLiteral())
        );
    }

    public function newFromRawContentProvider(): array
    {
        return [
            [
                'DF0A',
                self::XSD_NS . ' string',
                [ 'class' => StringSerializer::class ],
                'Bar  ',
                new StringLiteral('Bar')
            ],
            [
                '42',
                self::XSD_NS . ' nonNegativeInteger',
                [
                    'class' => NonNegativeIntegerSerializer::class,
                    'encoding' => 'BCD'
                ],
                "\x12\x34\x56",
                new NonNegativeIntegerLiteral(123456)
            ]
        ];
    }

    /**
     * @dataProvider equalsProvider
     */
    public function testEquals(
        $deInstance1,
        $deInstance2,
        $expectedResult
    ): void {
        $this->assertTrue($deInstance1->equals($deInstance1));
        $this->assertTrue($deInstance2->equals($deInstance2));

        $this->assertSame($expectedResult, $deInstance1->equals($deInstance2));
        $this->assertSame($expectedResult, $deInstance2->equals($deInstance1));
    }

    public function equalsProvider(): array
    {
        $schema = (new SchemaFactory())->getMainSchema();

        $tlvDataElements = [];

        $tlvDataElements[1] = new PrimitiveTlvDataElement(
            Tag::newFromHex('C0'),
            new DataElement($schema->getGlobalType(self::XSD_NS . ' string')),
            new StringSerializer()
        );

        $tlvDataElements[2] = new PrimitiveTlvDataElement(
            Tag::newFromHex('C0'),
            new DataElement($schema->getGlobalType(self::XSD_NS . ' string')),
            new StringSerializer()
        );

        $tlvDataElements[3] = new PrimitiveTlvDataElement(
            Tag::newFromHex('C1'),
            new DataElement($schema->getGlobalType(self::XSD_NS . ' string')),
            new StringSerializer()
        );

        $tlvDataElements[4] = new PrimitiveTlvDataElement(
            Tag::newFromHex('C0'),
            new DataElement($schema->getGlobalType(self::XSD_NS . ' token')),
            new StringSerializer()
        );

        $tlvDataElements[5] = new PrimitiveTlvDataElement(
            Tag::newFromHex('C0'),
            new DataElement($schema->getGlobalType(self::XSD_NS . ' string')),
            StringSerializer::newFromProps([ 'lengthRange' => [ 1, 10 ] ])
        );

        $tlvDataElements[6] = new PrimitiveTlvDataElement(
            Tag::newFromHex('C0'),
            new DataElement($schema->getGlobalType(self::XSD_NS . ' string')),
            new StringSerializer(),
            'an'
        );

        $literals = [];

        $literals[1] =
            new StringLiteral('foo', self::XSD_NS . '#token');

        $literals[2] =
            new StringLiteral('foo', self::XSD_NS . '#Name');

        $literals[3] =
            new StringLiteral('bar', self::XSD_NS . '#token');

        $tests = [
            [ 1, 1, 1, 2, true ],
            [ 1, 1, 1, 3, false ],
            [ 1, 1, 2, 1, true ],
            [ 1, 1, 2, 2, true ],
            [ 1, 1, 2, 3, false ],
            [ 1, 1, 3, 1, false ],
            [ 1, 1, 3, 2, false ],
            [ 1, 1, 3, 3, false ],
            [ 1, 1, 4, 1, false ],
            [ 1, 1, 4, 2, false ],
            [ 1, 1, 4, 3, false ],
            [ 1, 1, 5, 1, false ],
            [ 1, 1, 5, 2, false ],
            [ 1, 1, 5, 3, false ],
            [ 1, 1, 6, 1, false ],
            [ 1, 1, 6, 2, false ],
            [ 1, 1, 6, 3, false ]
        ];

        $data = [];

        foreach ($tests as $test) {
            [ $d1, $l1, $d2, $l2, $expectedResult ] = $test;

            $data[] = [
                PrimitiveTlvDeInstance::newFromLiteral(
                    $tlvDataElements[$d1],
                    $literals[$l1]
                ),
                PrimitiveTlvDeInstance::newFromLiteral(
                    $tlvDataElements[$d2],
                    $literals[$l2]
                ),
                $expectedResult
            ];
        }

        return $data;
    }

    public function testEqualsRawContent(): void
    {
        $tlvDataElement = new PrimitiveTlvDataElement(
            Tag::newFromHex('C0'),
            new DataElement(
                self::$schema_->getGlobalType(self::XSD_NS . ' integer')
            ),
            IntegerSerializer::newFromProps([ 'encoding' => 'BIG-ENDIAN' ])
        );

        $tlvDeInstance1 = PrimitiveTlvDeInstance::newFromRawContent(
            $tlvDataElement,
            ImmutableBinaryString::newFromHex('01')
        );

        $tlvDeInstance2 = PrimitiveTlvDeInstance::newFromRawContent(
            $tlvDataElement,
            ImmutableBinaryString::newFromHex('0001')
        );

        $this->assertTrue(
            $tlvDeInstance1->getLiteral()->equals($tlvDeInstance2->getLiteral())
        );

        $this->assertFalse($tlvDeInstance1->equals($tlvDeInstance2));
    }
}
