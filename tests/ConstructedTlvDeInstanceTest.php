<?php

namespace alcamo\x690;

use PHPUnit\Framework\TestCase;
use alcamo\binary_data\ImmutableBinaryString;
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
    public const XSD_NS = AbstractTlvDataElement::XSD_NS;

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
            $this->assertSame($child, $tlvDeInstance->getChildren()[$key]);
        }

        $this->assertEquals(
            ImmutableBinaryString::newFromHex($expectedRawContent),
            $tlvDeInstance->getRawContent()
        );
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
            'Invalid type "alcamo\binary_data\ImmutableBinaryString", expected '
                . 'one of "alcamo\x690\AbstractTlvDeInstance" for key "foo"'
        );

        new ConstructedTlvDeInstance(
            $tlvDe,
            new ImmutableBinaryString(),
            [ 'foo' => new ImmutableBinaryString() ]
        );
    }
}
