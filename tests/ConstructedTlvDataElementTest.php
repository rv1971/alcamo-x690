<?php

namespace alcamo\x690;

use PHPUnit\Framework\TestCase;
use alcamo\data_element\DataElement;
use alcamo\dom\schema\SchemaFactory;
use alcamo\exception\DataValidationFailed;

class ConstructedTlvDataElementTest extends TestCase
{
    public const XSD_NS = ConstructedTlvDataElement::XSD_NS;

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
        $expectedLabel
    ) {
        $tag = new Tag(hex2bin($tagString));

        $dataElement = new DataElement(
            self::$schema_->getGlobalType($typeXName),
            $rdfaData
        );

        $tlvDe = new ConstructedTlvDataElement($tag, $dataElement);

        $this->assertSame($tag, $tlvDe->getTag());

        $this->assertSame($dataElement, $tlvDe->getDataElement());

        $this->assertSame($dataElement->getDatatype(), $tlvDe->getDatatype());

        $this->assertSame(
            $dataElement->getRdfaData(),
            $tlvDe->getRdfaData()
        );

        $this->assertSame($expectedLabel, $tlvDe->getLabel());
    }

    public function constructProvider(): array
    {
        return [
            [
                'A0',
                self::XSD_NS . ' hexBinary',
                [ [ 'rdfs:label', 'Lorem ipsum' ] ],
                'Lorem ipsum (b)'
            ]
        ];
    }

    public function testConstructException1(): void
    {
        $tag = new Tag("\xC0");

        $dataElement = new DataElement(
            self::$schema_->getGlobalType(self::XSD_NS . ' hexBinary')
        );

        $this->expectException(DataValidationFailed::class);

        $this->expectExceptionMessage(
            "Validation failed; tag 'C0' is primitive"
        );

        new ConstructedTlvDataElement($tag, $dataElement);
    }

    public function testConstructException2(): void
    {
        $tag = new Tag("\xA0");

        $dataElement = new DataElement(
            self::$schema_->getGlobalType(self::XSD_NS . ' string')
        );

        $this->expectException(DataValidationFailed::class);

        $this->expectExceptionMessage(
            'Validation failed; the data element datatype '
                . 'http://www.w3.org/2001/XMLSchema string is not equal to '
                . 'or derived from http://www.w3.org/2001/XMLSchema hexBinary'
        );

        new ConstructedTlvDataElement($tag, $dataElement);
    }
}
