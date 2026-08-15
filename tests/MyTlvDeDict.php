<?php

namespace alcamo\x690;

use alcamo\data_element\{ConstructedDataElement, DataElement};
use alcamo\rdf_literal_serializer\{ConstructedSerializer, IntegerSerializer};

class MyTlvDeDict extends UniversalClassTlvDeDict
{
    private $tlvDataElement_;

    public function __construct(?TlvDeWorkbench $tlvDeWorkbench = null)
    {
        parent::__construct($tlvDeWorkbench);

        $integerType = $this->tlvDeWorkbench_->getSchema()
            ->getGlobalType(self::XSD_NS . ' integer');

        $dataElement1 = new DataElement(
            $integerType,
            [ [ 'rdfs:label', 'Foo' ] ]
        );

        $dataElement2 = new DataElement(
            $integerType,
            [ [ 'rdfs:label', 'Bar' ] ]
        );

        $serializer = new IntegerSerializer(
            $integerType->getXName(),
            'BIG-ENDIAN',
            [ 1, 1 ]
        );

        $this->tlvDataElement_ = new PrimitiveTlvDataElement(
            Tag::newFromHex('C0'),
            new ConstructedDataElement(
                [ $dataElement1, $dataElement2 ],
                $this->tlvDeWorkbench_->getSchema()
                    ->getGlobalType(self::XSD_NS . ' hexBinary'),
                [ [ 'rdfs:label', 'Foo/Bar' ] ]
            ),
            ConstructedSerializer::newFromProps(
                [
                    'datatypeXName' => self::XSD_NS . ' hexBinary',
                    'serializers' => [ $serializer, $serializer ],
                    'lengthRange' => [ 2, 2 ]
                ]
            )
        );
    }

    public function lookup($tag, $parent = null): TlvDataElementInterface
    {
        if ($tag == 'C0') {
            return $this->tlvDataElement_;
        }

        return parent::lookup($tag, $parent);
    }
}
