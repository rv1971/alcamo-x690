<?php

namespace alcamo\x690;

use PHPUnit\Framework\TestCase;
use alcamo\binary_data\BinaryString;
use alcamo\data_element\{
    AbstractSerializer,
    DataElement,
    DateTimeSerializer,
    IntegerSerializer
};
use alcamo\dom\schema\SchemaFactory;
use alcamo\exception\InvalidType;
use alcamo\rdf_literal\{
    DateLiteral,
    IntegerLiteral
};

class ConstructedTlvDeInstanceTest extends TestCase
{
    public const XSD_NS = PrimitiveTlvDataElement::XSD_NS;

    private static $schema_;

    public static function setUpBeforeClass(): void
    {
        self::$schema_ = (new SchemaFactory())->getMainSchema();
    }

    /**
     * @dataProvider newFromChildrenProvider
     */
    public function testNewFromChildren(
        $tagString,
        $childrenData,
        $expectedRawContent
    ) {
        $tlvDe = new ConstructedTlvDataElement(
            Tag::newFromHex($tagString),
            new DataElement(
                self::$schema_->getGlobalType(self::XSD_NS . ' hexBinary')
            ),
        );

        $children = [];

        foreach ($childrenData as $childData) {
            [ $tagString, $typeXName, $serializerProps, $literal ] = $childData;

            $children[] = PrimitiveTlvDeInstance::newFromLiteral(
                new PrimitiveTlvDataElement(
                    Tag::newFromHex($tagString),
                    new DataElement(self::$schema_->getGlobalType($typeXName)),
                    AbstractSerializer::createFromProps($serializerProps)
                ),
                $literal
            );
        }

        $tlvDeInstance =
            ConstructedTlvDeInstance::newFromChildren($tlvDe, $children);

        $this->assertSame(
            count($children),
            count($tlvDeInstance->getChildren())
        );

        foreach ($children as $key => $child) {
            $this->assertNotSame($child, $tlvDeInstance->getChildren()[$key]);
            $this->assertEquals($child, $tlvDeInstance->getChildren()[$key]);
        }

        $this->assertEquals(
            BinaryString::newFromHex($expectedRawContent),
            $tlvDeInstance->getRawContent()
        );

        $tlvDeInstance2 = clone $tlvDeInstance;

        $this->assertSame(
            count($tlvDeInstance2->getChildren()),
            count($tlvDeInstance->getChildren())
        );

        foreach ($tlvDeInstance->getChildren() as $key => $child) {
            $this->assertNotSame($child, $tlvDeInstance2->getChildren()[$key]);
            $this->assertEquals($child, $tlvDeInstance2->getChildren()[$key]);
        }
    }

    public function newFromChildrenProvider(): array
    {
        return [
            [ 'A0', [], '' ],
            [
                'A1',
                [
                    [
                        'C0',
                        self::XSD_NS . ' date',
                        [
                            'class' => DateTimeSerializer::class,
                            'datatypeXName' => self::XSD_NS . ' date',
                            'encoding' => 'BCD'
                        ],
                        new DateLiteral('2026-05-20')
                    ],
                    [
                        'C1',
                        self::XSD_NS . ' byte',
                        [
                            'class' => IntegerSerializer::class,
                            'encoding' => 'BIG-ENDIAN'
                        ],
                        new IntegerLiteral(-2)
                    ]
                ],
                'C00420260520' . 'C101FE'
            ]
        ];
    }

    public function testConstructException(): void
    {
        $tlvDe = new ConstructedTlvDataElement(
            Tag::newFromHex('A0'),
            new DataElement(
                self::$schema_->getGlobalType(self::XSD_NS . ' hexBinary')
            ),
        );

        $this->expectException(InvalidType::class);

        $this->expectExceptionMessage(
            'Invalid type "alcamo\binary_data\BinaryString", expected one of '
                . '"alcamo\x690\AbstractTlvDeInstance" for key "foo"'
        );

        new ConstructedTlvDeInstance(
            $tlvDe,
            new BinaryString(),
            [ 'foo' => new BinaryString() ]
        );
    }
}
