<?php

namespace alcamo\x690;

use alcamo\binary_data\ImmutableBinaryString;
use alcamo\rdf_literal\BitStringLiteral;
use alcamo\rdf_literal_serializer\{
    BinarySerializer,
    BitStringSerializer,
    DateTimeSerializer,
    IntegerSerializer,
    NonNegativeIntegerSerializer,
    StringSerializer
};

/**
 * @brief Data element dictionary with universal class data elements
 *
 * @date Last reviewed 2026-05-29
 */
class UniversalClassTlvDeDict implements TlvDeDictInterface
{
    public const DICT = [
        '00' => [
            'datatypeXName' => self::XSD_NS . ' hexBinary',
            'serializer' => [
                'class' => BinarySerializer::class,
                'lengthRange' => [ 0, 0 ]
            ],
            'rdfaData' => [ [ 'rdfs:label', 'End-of-Content' ] ]
        ],
        '01' => [
            'datatypeXName' => self::XSD_NS . ' boolean',
            'serializer' => [
                'class' => NonNegativeIntegerSerializer::class,
                'encoding' => 'BIG-ENDIAN',
                'lengthRange' => [ 1, 1 ]
            ],
            'rdfaData' => [ [ 'rdfs:label', 'Boolean' ] ]
        ],
        '02' => [
            'datatypeXName' => self::XSD_NS . ' integer',
            'serializer' => [
                'class' => IntegerSerializer::class,
                'encoding' => 'BIG-ENDIAN'
            ],
            'rdfaData' => [ [ 'rdfs:label', 'Integer' ] ]
        ],
        '03' => [
            'datatypeXName' => BitStringLiteral::DEFAULT_DATATYPE_XNAME,
            'serializer' => [
                'class' => BitStringSerializer::class,
                'encoding' => 'X.690'
            ],
            'rdfaData' => [ [ 'rdfs:label', 'Bitstring' ] ]
        ],
        '04' => [
            'datatypeXName' => self::XSD_NS . ' hexBinary',
            'serializer' => [ 'class' => BinarySerializer::class ],
            'rdfaData' => [ [ 'rdfs:label', 'Octetstring' ] ]
        ],
        '05' => [
            'datatypeXName' => self::XSD_NS . ' hexBinary',
            'serializer' => [
                'class' => BinarySerializer::class,
                'lengthRange' => [ 0, 0 ]
            ],
            'rdfaData' => [ [ 'rdfs:label', 'Null' ] ]
        ],
        '06' => [
            'datatypeXName' => self::XSD_NS . ' string',
            'serializer' => [
                'class' => StringSerializer::class,
                'encoding' => 'ASCII'
            ],
            'rdfaData' => [ [ 'rdfs:label', 'Object identifier' ] ]
        ],
        '07' => [
            'datatypeXName' => self::XSD_NS . ' string',
            'serializer' => [
                'class' => StringSerializer::class,
                'encoding' => 'ASCII'
            ],
            'rdfaData' => [ [ 'rdfs:label', 'Object descriptor' ] ]
        ],
        '0A' => [
            'datatypeXName' => self::XSD_NS . ' integer',
            'serializer' => [
                'class' => IntegerSerializer::class,
                'encoding' => 'BIG-ENDIAN'
            ],
            'rdfaData' => [ [ 'rdfs:label', 'Enumerated' ] ]
        ],
        '0C' => [
            'datatypeXName' => self::XSD_NS . ' string',
            'serializer' => [
                'class' => StringSerializer::class,
                'encoding' => 'UTF-8'
            ],
            'rdfaData' => [ [ 'rdfs:label', 'UTF8String' ] ]
        ],
        '0D' => [
            'datatypeXName' => self::XSD_NS . ' string',
            'serializer' => [
                'class' => StringSerializer::class,
                'encoding' => 'ASCII'
                ],
            'rdfaData' => [ [ 'rdfs:label', 'Relative object identifier' ] ]
        ],
        '12' => [
            'datatypeXName' => self::XSD_NS . ' string',
            'serializer' => [
                'class' => StringSerializer::class,
                'encoding' => 'ASCII'
            ],
            'rdfaData' => [ [ 'rdfs:label', 'NumericString' ] ]
        ],
        '13' => [
            'datatypeXName' => self::XSD_NS . ' string',
            'serializer' => [
                'class' => StringSerializer::class,
                'encoding' => 'ASCII'
            ],
            'rdfaData' => [ [ 'rdfs:label', 'PrintableString' ] ]
        ],
        '17' => [
            'datatypeXName' => self::XSD_NS . ' dateTime',
            'serializer' => [
                'class' => DateTimeSerializer::class,
                'posixFormat' => '%y%m%d%H%M%SZ',
                'asUtc' => true
            ],
            'format' => 'an',
            'rdfaData' => [ [ 'rdfs:label', 'UTCTime' ] ]
        ],
        '18' => [
            'datatypeXName' => self::XSD_NS . ' dateTime',
            'serializer' => [
                'class' => DateTimeSerializer::class,
                'posixFormat' => '%Y%m%d%H%M%SZ',
                'asUtc' => true
            ],
            'format' => 'an',
            'rdfaData' => [ [ 'rdfs:label', 'GeneralizedTime' ] ]
        ],
        '19' => [
            'datatypeXName' => self::XSD_NS . ' string',
            'serializer' => [
                'class' => StringSerializer::class,
                'encoding' => 'UTF-8'
            ],
            'rdfaData' => [ [ 'rdfs:label', 'GraphicString' ] ]
        ],
        '1A' => [
            'datatypeXName' => self::XSD_NS . ' string',
            'serializer' => [
                'class' => StringSerializer::class,
                'encoding' => 'UTF-8'
            ],
            'rdfaData' => [ [ 'rdfs:label', 'VisibleString' ] ]
        ],
        '1B' => [
            'datatypeXName' => self::XSD_NS . ' string',
            'serializer' => [
                'class' => StringSerializer::class,
                'encoding' => 'UTF-8'
            ],
            'rdfaData' => [ [ 'rdfs:label', 'GeneralString' ] ]
        ],
        '1C' => [
            'datatypeXName' => self::XSD_NS . ' string',
            'serializer' => [
                'class' => StringSerializer::class,
                'encoding' => 'UTF-8'
            ],
            'rdfaData' => [ [ 'rdfs:label', 'UniversalString' ] ]
        ],
        '1E' => [
            'datatypeXName' => self::XSD_NS . ' string',
            'serializer' => [
                'class' => StringSerializer::class,
                'encoding' => 'UTF-8'
            ],
            'rdfaData' => [ [ 'rdfs:label', 'BMPString' ] ]
        ],
        '1F1F' => [
            'datatypeXName' => self::XSD_NS . ' date',
            'serializer' => [
                'class' => DateTimeSerializer::class,
                'posixFormat' => '%Y%m%d'
            ],
            'format' => 'an',
            'rdfaData' => [ [ 'rdfs:label', 'DATE' ] ]
        ],
        '1F20' => [
            'datatypeXName' => self::XSD_NS . ' time',
            'serializer' => [
                'class' => DateTimeSerializer::class,
                'posixFormat' => '%H%M%S'
            ],
            'format' => 'an',
            'rdfaData' => [ [ 'rdfs:label', 'TIME-OF-DAY' ] ]
        ],
        '1F21' => [
            'datatypeXName' => self::XSD_NS . ' dateTime',
            'serializer' => [
                'class' => DateTimeSerializer::class,
                'posixFormat' => '%Y%m%d%H%M%S'
            ],
            'format' => 'an',
            'rdfaData' => [ [ 'rdfs:label', 'DATE-TIME' ] ]
        ],
        '1F23' => [
            'datatypeXName' => self::XSD_NS . ' string',
            'serializer' => [
                'class' => StringSerializer::class,
                'encoding' => 'ASCII'
            ],
            'rdfaData' => [
                [ 'rdfs:label', 'OID internationalized resource identifier' ]
            ]
        ],
        '1F24' => [
            'datatypeXName' => self::XSD_NS . ' string',
            'serializer' => [
                'class' => StringSerializer::class,
                'encoding' => 'ASCII'
            ],
            'rdfaData' => [
                [
                    'rdfs:label',
                    'Relative OID internationalized resource identifier'
                ]
            ]
        ],
        '3F28' => [
            'datatypeXName' => self::XSD_NS . ' hexBinary',
            'rdfaData' => [ [ 'rdfs:label', 'External' ] ]
        ],
        '3F2B' => [
            'datatypeXName' => self::XSD_NS . ' hexBinary',
            'rdfaData' => [ [ 'rdfs:label', 'Embedded-pdv' ] ]
        ],
        '3F30' => [
            'datatypeXName' => self::XSD_NS . ' hexBinary',
            'rdfaData' => [ [ 'rdfs:label', 'Sequence' ] ]
        ],
        '3F31' => [
            'datatypeXName' => self::XSD_NS . ' hexBinary',
            'rdfaData' => [ [ 'rdfs:label', 'Set' ] ]
        ],
        'UNKNOWN-PRIMITIVE' => [
            'datatypeXName' => self::XSD_NS . ' hexBinary',
            'serializer' => [ 'class' => BinarySerializer::class ],
            'rdfaData' => [ [ 'rdfs:label', 'Unknown' ] ]
        ],
        'UNKNOWN-CONSTRUCTED' => [
            'datatypeXName' => self::XSD_NS . ' hexBinary',
            'rdfaData' => [ [ 'rdfs:label', 'Unknown' ] ]
        ]
    ];

    protected $tlvDeWorkbench_; ///< TlvDeWorkbench

    private $dictCache_ = [];

    private $eocDeInstance_; ///< PrimitiveDeInstance

    public function __construct(?TlvDeWorkbench $tlvDeWorkbench = null)
    {
        $this->tlvDeWorkbench_ =
            $tlvDeWorkbench ?? TlvDeWorkbench::getMainInstance();
    }

    public function getTlvDeWorkbench(): TlvDeWorkbench
    {
        return $this->tlvDeWorkbench_;
    }

    public function lookup($tag, $parent = null): TlvDataElementInterface
    {
        $tagString = (string)$tag;

        if (!($tag instanceof Tag)) {
            $tag = Tag::newFromHex($tag);
        }

        if (!isset($this->dictCache_[$tagString])) {
            $props = (object)(
                self::DICT[$tagString]
                    ?? self::DICT[
                        $tag->isConstructed()
                            ? 'UNKNOWN-CONSTRUCTED'
                            : 'UNKNOWN-PRIMITIVE'
                    ]
            );

            return $this->dictCache_[$tagString] = $this->tlvDeWorkbench_
                ->createTlvDataElementFromTagAndProps($tag, $props);
        }

        return $this->dictCache_[$tagString];
    }

    public function getEocDeInstance(): PrimitiveTlvDeInstance
    {
        if (!isset($this->eocDeInstance_)) {
            $this->eocDeInstance_ = PrimitiveTlvDeInstance::newFromRawContent(
                $this->lookup('00'),
                new ImmutableBinaryString()
            );
        }

        return $this->eocDeInstance_;
    }

    public function createPrimitiveTlvDeInstance(
        $value,
        $tag,
        $parent = null
    ): PrimitiveTlvDeInstance {
        $tlvDataElement = $this->lookup($tag, $parent);

        return PrimitiveTlvDeInstance::newFromLiteral(
            $tlvDataElement,
            $this->tlvDeWorkbench_
                ->createLiteral($value, $tlvDataElement->getDatatype())
        );
    }
}
