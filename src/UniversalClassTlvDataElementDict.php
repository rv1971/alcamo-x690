<?php

namespace alcamo\x690;

use alcamo\data_element\{
    BitStringSerializer,
    DataElement,
    LiteralWorkbench,
    NonNegativeIntegerSerializer
};
use alcamo\rdf_literal\BitStringLiteral;

class UniversalClassTlvDataElementDict implements TlvDataElementDictInterface
{
    public const DICT = [
        '00' => [
            'datatypeXName' => self::XSD_NS . ' hexBinary',
            'serializerClass' => BinarySerializer::class,
            'lengthRange' => [ 0, 0 ],
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
        '03' => [
            'datatypeXName' => BitStringLiteral::DEFAULT_DATATYPE_XNAME,
            'serializerClass' => BitStringSerializer::class,
            'encoding' => 'X.690',
            'rdfaData' => [ [ 'rdfs:label', 'Bitstring' ] ]
        ],
        '04' => [
            'datatypeXName' => self::XSD_NS . ' hexBinary',
            'serializerClass' => BinarySerializer::class,
            'rdfaData' => [ [ 'rdfs:label', 'Octet string' ] ]
        ],
        '05' => [
            'datatypeXName' => self::XSD_NS . ' hexBinary',
            'serializerClass' => BinarySerializer::class,
            'lengthRange' => [ 0, 0 ],
            'rdfaData' => [ [ 'rdfs:label', 'Null' ] ]
        ],
        '06' => [
            'datatypeXName' => self::XSD_NS . ' string',
            'serializerClass' => StringSerializer::class,
            'encoding' => 'ASCII',
            'rdfaData' => [ [ 'rdfs:label', 'Object identifier' ] ]
        ],
        '07' => [
            'datatypeXName' => self::XSD_NS . ' string',
            'serializerClass' => StringSerializer::class,
            'encoding' => 'ASCII',
            'rdfaData' => [ [ 'rdfs:label', 'Object descriptor' ] ]
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
        '0D' => [
            'datatypeXName' => self::XSD_NS . ' string',
            'serializerClass' => StringSerializer::class,
            'encoding' => 'ASCII',
            'rdfaData' => [ [ 'rdfs:label', 'Relative object identifier' ] ]
        ],
        '12' => [
            'datatypeXName' => self::XSD_NS . ' string',
            'serializerClass' => StringSerializer::class,
            'encoding' => 'ASCII',
            'rdfaData' => [ [ 'rdfs:label', 'NumericString' ] ]
        ],
        '13' => [
            'datatypeXName' => self::XSD_NS . ' string',
            'serializerClass' => StringSerializer::class,
            'encoding' => 'ASCII',
            'rdfaData' => [ [ 'rdfs:label', 'PrintableString' ] ]
        ],
        '23' => [
            'datatypeXName' => self::XSD_NS . ' dateTime',
            'serializerClass' => DateTimeSerializer::class,
            'posixFormat' => '%y%m%d%H%M%SZ',
            'asUtc' => true,
            'rdfaData' => [ [ 'rdfs:label', 'UTCTime' ] ]
        ],
        '24' => [
            'datatypeXName' => self::XSD_NS . ' dateTime',
            'serializerClass' => DateTimeSerializer::class,
            'posixFormat' => '%Y%m%d%H%M%SZ',
            'asUtc' => true,
            'rdfaData' => [ [ 'rdfs:label', 'GeneralizedTime' ] ]
        ],
        '19' => [
            'datatypeXName' => self::XSD_NS . ' string',
            'serializerClass' => StringSerializer::class,
            'encoding' => 'UTF-8',
            'rdfaData' => [ [ 'rdfs:label', 'GraphicString' ] ]
        ],
        '1A' => [
            'datatypeXName' => self::XSD_NS . ' string',
            'serializerClass' => StringSerializer::class,
            'encoding' => 'UTF-8',
            'rdfaData' => [ [ 'rdfs:label', 'VisibleString' ] ]
        ],
        '1B' => [
            'datatypeXName' => self::XSD_NS . ' string',
            'serializerClass' => StringSerializer::class,
            'encoding' => 'UTF-8',
            'rdfaData' => [ [ 'rdfs:label', 'GeneralString' ] ]
        ],
        '1C' => [
            'datatypeXName' => self::XSD_NS . ' string',
            'serializerClass' => StringSerializer::class,
            'encoding' => 'UTF-8',
            'rdfaData' => [ [ 'rdfs:label', 'UniversalString' ] ]
        ],
        '1E' => [
            'datatypeXName' => self::XSD_NS . ' string',
            'serializerClass' => StringSerializer::class,
            'encoding' => 'UTF-8',
            'rdfaData' => [ [ 'rdfs:label', 'BMPString' ] ]
        ],
        '1F' => [
            'datatypeXName' => self::XSD_NS . ' date',
            'serializerClass' => DateSerializer::class,
            'posixFormat' => '%Y%m%d',
            'rdfaData' => [ [ 'rdfs:label', 'DATE' ] ]
        ],
        '20' => [
            'datatypeXName' => self::XSD_NS . ' time',
            'serializerClass' => TimeSerializer::class,
            'posixFormat' => '%H%M%S',
            'rdfaData' => [ [ 'rdfs:label', 'TIME-OF-DAY' ] ]
        ],
        '21' => [
            'datatypeXName' => self::XSD_NS . ' dateTime',
            'serializerClass' => DateTimeSerializer::class,
            'posixFormat' => '%Y%m%d%H%M%S',
            'rdfaData' => [ [ 'rdfs:label', 'DATE-TIME' ] ]
        ],
        '23' => [
            'datatypeXName' => self::XSD_NS . ' string',
            'serializerClass' => StringSerializer::class,
            'encoding' => 'ASCII',
            'rdfaData' => [
                [ 'rdfs:label', 'OID internationalized resource identifier' ]
            ]
        ],
        '24' => [
            'datatypeXName' => self::XSD_NS . ' string',
            'serializerClass' => StringSerializer::class,
            'encoding' => 'ASCII',
            'rdfaData' => [
                [
                    'rdfs:label',
                    'Relative OID internationalized resource identifier'
                ]
            ]
        ],
        '28' => [
            'datatypeXName' => self::XSD_NS . ' hexBinary',
            'rdfaData' => [ [ 'rdfs:label', 'External' ] ]
        ],
        '2B' => [
            'datatypeXName' => self::XSD_NS . ' hexBinary',
            'rdfaData' => [ [ 'rdfs:label', 'Embedded-pdv' ] ]
        ],
        '30' => [
            'datatypeXName' => self::XSD_NS . ' hexBinary',
            'rdfaData' => [ [ 'rdfs:label', 'Sequence' ] ]
        ],
        '31' => [
            'datatypeXName' => self::XSD_NS . ' hexBinary',
            'rdfaData' => [ [ 'rdfs:label', 'Set' ] ]
        ],
        'UNKNOWN-PRIMITIVE' => [
            'datatypeXName' => self::XSD_NS . ' hexBinary',
            'serializerClass' => BinarySerializer::class,
            'rdfaData' => [ [ 'rdfs:label', 'Unknown' ] ]
        ],
        'UNKNOWN-CONSTRUCTED' => [
            'datatypeXName' => self::XSD_NS . ' hexBinary',
            'rdfaData' => [ [ 'rdfs:label', 'Unknown' ] ]
        ]
    ];

    private $literalWorkbench_; ///< LiteralWorkbench

    private $dictCache_ = [];

    public function __construct(?LiteralWorkbench $literalWorkbench = null)
    {
        $this->literalWorkbench_ =
            $literalWorkbench ?? LiteralWorkbench::getMainInstance();
    }

    public function lookup(
        Tag $tag,
        ?string $parent = null
    ): TlvDataElementInterface {
        $tagString = (string)$tag;

        if (!isset($this->dictCache_[$tagString])) {
            $props = (object)self::DICT[$tagString] ?? null;

            if (!isset($props)) {
                $props = (object)(
                    self::DICT[
                        $tag->isConstructed()
                            ? 'UNKNOWN-CONSTRUCTED'
                            : 'UNKNOWN-PRIMITIVE'
                    ]
                );
            }

            $props->literalWorkbench = $this->literalWorkbench_;

            $dataElement = new DataElement(
                $this->literalWorkbench_->getSchema()
                    ->getGlobalType($props->datatypeXName),
                $props->rdfaData ?? null
            );

            if ($tag->isConstructed()) {
                return $this->dictCache_[$tagString] =
                    new ConstructedTlvDataElement($tag, $dataElement);
            } else {
                $serializerClass = $props->serializerClass;

                return $this->dictCache_[$tagString] =
                    new PrimitiveTlvDataElement(
                        $tag,
                        $dataElement,
                        $serializerClass::newFromProps($props),
                        $props->format ?? null
                    );
            }
        }

        return $this->dictCache_[$tagString];
    }
}
