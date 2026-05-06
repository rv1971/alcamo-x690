<?php

namespace alcamo\x690;

use alcamo\data_element\{
    DataElement,
    FactoryGroup,
    NonNegativeIntegerSerializer
};
use alcamo\xml\NamespaceConstantsInterface;

class UniversalClassTlvDataElementDict implements
    NamespaceConstantsInterface,
    TlvDataElementDictInterface
{
    public const DICT = [
        '00' => [
            'datatypeXName' => self::XSD_NS . ' hexBinary',
            'serializerClass' => BinarySerializer::class,
            'length' => [ 0, 0 ],
            'rdfaData' => [ [ 'rdfs:label', 'End-of-Content' ] ]
        ],
        '01' => [
            'datatypeXName' => self::XSD_NS . ' boolean',
            'serializerClass' => NonNegativeIntegerSerializer::class,
            'encoding' => 'BIG-ENDIAN',
            'rdfaData' => [ [ 'rdfs:label', 'Boolean' ] ]
        ],
        '02' => [
            'datatypeXName' => self::XSD_NS . ' integer',
            'serializerClass' => IntegerSerializer::class,
            'encoding' => 'BIG-ENDIAN',
            'rdfaData' => [ [ 'rdfs:label', 'Integer' ] ]
        ],
        /* 03 bitstring needs a special serializer, even a special literal
         * derived from DigitsStringLiteral, which might use a string of 0 and
         * 1 as its internal representation and have methods
         * newFromBinaryString() and toBinaryString(); might be useful for
         * bitmaps and would be inline with SPICA slot handling */
        '04' => [
            'datatypeXName' => self::XSD_NS . ' hexBinary',
            'serializerClass' => BinarySerializer::class,
            'rdfaData' => [ [ 'rdfs:label', 'Octet string' ] ]
        ],
        '05' => [
            'datatypeXName' => self::XSD_NS . ' hexBinary',
            'serializerClass' => BinarySerializer::class,
            'length' => [ 0, 0 ],
            'rdfaData' => [ [ 'rdfs:label', 'Null' ] ]
        ],
        '0A' => [
            'datatypeXName' => self::XSD_NS . ' integer',
            'serializerClass' => IntegerSerializer::class,
            'encoding' => 'BIG-ENDIAN',
            'rdfaData' => [ [ 'rdfs:label', 'Enumerated' ] ]
        ],
        '0C' => [
            'datatypeXName' => self::XSD_NS . ' string',
            'serializerClass' => StringSerializer::class,
            'encoding' => 'UTF-8',
            'rdfaData' => [ [ 'rdfs:label', 'UTF8String' ] ]
        ],
        '10' => [
            'datatypeXName' => self::XSD_NS . ' hexBinary',
            'serializerClass' => BinarySerializer::class,
            'rdfaData' => [ [ 'rdfs:label', 'Sequence' ] ]
        ],
        '11' => [
            'datatypeXName' => self::XSD_NS . ' hexBinary',
            'serializerClass' => BinarySerializer::class,
            'rdfaData' => [ [ 'rdfs:label', 'Set' ] ]
        ],
        /* unclear whether the following are correct */
        '1F' => [
            'datatypeXName' => self::XSD_NS . ' date',
            'serializerClass' => DateSerializer::class,
            'posixFormat' => '%Y%m%d',
            'rdfaData' => [ [ 'rdfs:label', 'Date' ] ]
        ],
        '20' => [
            'datatypeXName' => self::XSD_NS . ' time',
            'serializerClass' => TimeSerializer::class,
            'posixFormat' => '%H%M%S',
            'rdfaData' => [ [ 'rdfs:label', 'Time-of-day' ] ]
        ],
        '21' => [
            'datatypeXName' => self::XSD_NS . ' dateTime',
            'serializerClass' => DateTimeSerializer::class,
            'posixFormat' => '%Y%m%d%H%M%S',
            'rdfaData' => [ [ 'rdfs:label', 'Date-time' ] ]
        ],

    ];

    private $factoryGroup_; ///< FactoryGroup

    private $dictCache_ = [];

    public function __construct(?FactoryGroup $factoryGroup = null)
    {
        $this->factoryGroup_ = $factoryGroup ?? FactoryGroup::getMainInstance();
    }

    public function lookup(
        Tag $tag,
        ?string $parent = null
    ): TlvDataElementInterface {
        if (!isset($this->dictCache_[(string)$tag])) {
            $dictData = (object)(self::DICT[(string)$tag] ?? self::DICT['*']);

            $dataElement = new DataElement(
                $this->factoryGroup_->getSchema()
                    ->getGlobalType($dictData->type),
                $dictData->rdfaData ?? null
            );

            $serializerClass = $dictData->serializerClass;

            $dictData->factoryGroup = $this->factoryGroup_;

            return $this->dictCache_[(string)$tag] =
                new TlvDataElement(
                    $tag,
                    $dataElement,
                    $serializerClass::newFromProps($dictData),
                    $dictData->format ?? null
                );
        }

        return $this->dictCache_[$tag];
    }
}
