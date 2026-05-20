<?php

namespace alcamo\x690;

use PHPUnit\Framework\TestCase;
use alcamo\binary_data\BinaryString;
use alcamo\data_element\{
    AbstractSerializer,
    BinarySerializer,
    DataElement,
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
        $expectedRawContent
    ) {
        $tlvDe = new PrimitiveTlvDataElement(
            Tag::newFromHex($tagString),
            new DataElement(self::$schema_->getGlobalType($typeXName)),
            AbstractSerializer::createFromProps($serializerProps),
        );

        $tlvDeInstance =
            PrimitiveTlvDeInstance::newFromLiteral($tlvDe, $literal);

        $this->assertSame($tlvDe, $tlvDeInstance->getDataElement());

        $this->assertNotSame($literal, $tlvDeInstance->getLiteral());

        $this->assertEquals($literal, $tlvDeInstance->getLiteral());

        $expectedRawContent = new BinaryString($expectedRawContent);

        $this->assertEquals(
            $expectedRawContent,
            $tlvDeInstance->getRawContent()
        );

        $this->assertSame(
            count($expectedRawContent),
            $tlvDeInstance->getLength()
        );

        $tlvDeInstance2 = clone $tlvDeInstance;

        $this->assertNotSame(
            $tlvDeInstance->getDataElement(),
            $tlvDeInstance2->getDataElement()
        );

        $this->assertEquals(
            $tlvDeInstance->getDataElement(),
            $tlvDeInstance2->getDataElement()
        );

        $this->assertNotSame(
            $tlvDeInstance->getRawContent(),
            $tlvDeInstance2->getRawContent()
        );

        $this->assertEquals(
            $tlvDeInstance->getRawContent(),
            $tlvDeInstance2->getRawContent()
        );

        $this->assertNotSame(
            $tlvDeInstance->getLiteral(),
            $tlvDeInstance2->getLiteral()
        );

        $this->assertEquals(
            $tlvDeInstance->getLiteral(),
            $tlvDeInstance2->getLiteral()
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
                "\xAB\xCD\x00\x00\x00",
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

        $rawContent = new BinaryString($rawContent);

        $tlvDeInstance = PrimitiveTlvDeInstance::newFromRawContent(
            $tlvDe,
            $rawContent
        );

        $this->assertSame($tlvDe, $tlvDeInstance->getDataElement());

        $this->assertNotSame($rawContent, $tlvDeInstance->getRawContent());

        $this->assertEquals($rawContent, $tlvDeInstance->getRawContent());

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
}
