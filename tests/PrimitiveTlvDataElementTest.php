<?php

namespace alcamo\x690;

use PHPUnit\Framework\TestCase;
use alcamo\data_element\DataElement;
use alcamo\dom\schema\SchemaFactory;
use alcamo\exception\{DataValidationFailed, InvalidType};
use alcamo\rdf_literal\DigitStringLiteral;
use alcamo\rdf_literal_serializer\{
    AbstractSerializer,
    BinarySerializer,
    DigitStringSerializer,
    NonNegativeIntegerSerializer,
    StringSerializer
};

class PrimitiveTlvDataElementTest extends TestCase
{
    public const XSD_NS = PrimitiveTlvDataElement::XSD_NS;

    private static $schema_;

    public static function setUpBeforeClass(): void
    {
        self::$schema_ = (new SchemaFactory())->getMainSchema();
    }

    /**
     * @dataProvider constructProvider
     */
    public function testConstruct(
        $tagString,
        $typeXName,
        $rdfaData,
        $serializerProps,
        $format,
        $expectedLabel
    ) {
        $tag = new Tag(hex2bin($tagString));

        $dataElement = new DataElement(
            self::$schema_->getGlobalType($typeXName),
            $rdfaData
        );

        $serializer = AbstractSerializer::createFromProps($serializerProps);

        $tlvDe = new PrimitiveTlvDataElement(
            $tag,
            $dataElement,
            $serializer,
            $format
        );

        $this->assertSame($tag, $tlvDe->getTag());

        $this->assertSame($dataElement, $tlvDe->getDataElement());

        $this->assertSame($dataElement->getDatatype(), $tlvDe->getDatatype());

        $this->assertSame($dataElement->getRdfaData(), $tlvDe->getRdfaData());

        $this->assertSame($expectedLabel, $tlvDe->getLabel());
        $this->assertSame($serializer, $tlvDe->getSerializer());
    }

    public function constructProvider(): array
    {
        return [
            [
                'DF0A',
                self::XSD_NS . ' string',
                [ [ 'rdfs:label', 'Lorem ipsum' ] ],
                [
                    'class' => StringSerializer::class,
                    'lengthRange' => [ 1, 16 ]
                ],
                'anp',
                'Lorem ipsum (anp 1-16)'
            ],
            [
                '84',
                self::XSD_NS . ' hexBinary',
                null,
                [
                    'class' => BinarySerializer::class,
                    'lengthRange' => [ 5, 16 ]
                ],
                null,
                'hexBinary (b 5-16)',
            ],
            [
                '42',
                self::XSD_NS . ' nonNegativeInteger',
                null,
                [
                    'class' => NonNegativeIntegerSerializer::class,
                    'encoding' => 'BCD',
                    'lengthRange' => [ 6, 6 ]
                ],
                null,
                'nonNegativeInteger (n 6)',
            ],
            [
                'DF2C',
                DigitStringLiteral::DEFAULT_DATATYPE_XNAME,
                null,
                [
                    'class' => DigitStringSerializer::class,
                    'encoding' => 'COMPRESSED-BCD',
                    'lengthRange' => [ 1, 10 ]
                ],
                null,
                'String made of decimal digits (cn 1-10)',
            ],
            [
                '5F2D',
                self::XSD_NS . ' string',
                null,
                [
                    'class' => StringSerializer::class,
                    'encoding' => 'ASCII',
                    'lengthRange' => [ 2, 8 ]
                ],
                null,
                'string (ans 2-8)',
            ]
        ];
    }

    public function testConstructException1(): void
    {
        $tag = new Tag("\xA0");

        $dataElement = new DataElement(
            self::$schema_->getGlobalType(self::XSD_NS . ' hexBinary')
        );

        $serializer = new BinarySerializer();

        $this->expectException(DataValidationFailed::class);

        $this->expectExceptionMessage(
            "Validation failed; tag 'A0' is constructed"
        );

        new PrimitiveTlvDataElement($tag, $dataElement, $serializer);
    }

    public function testConstructException2(): void
    {
        $tag = new Tag("\xC0");

        $dataElement = new DataElement(
            self::$schema_->getGlobalType(self::XSD_NS . ' hexBinary')
        );

        $serializer = new StringSerializer();

        $this->expectException(InvalidType::class);

        $this->expectExceptionMessage(
            'Invalid type "http://www.w3.org/2001/XMLSchema hexB...", '
                . 'expected one of "derived from http://www.w3.org/2001/X..."'
        );

        new PrimitiveTlvDataElement($tag, $dataElement, $serializer);
    }
}
