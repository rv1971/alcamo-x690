<?php

namespace alcamo\x690;

use alcamo\data_element\DeWorkbench;
use alcamo\dom\schema\Schema;
use alcamo\rdf_literal\LiteralFactory as RdfLiteralFactory;
use alcamo\rdf_literal_serializer\AbstractSerializer;
use alcamo\rdf_literal_workbench\LiteralWorkbench;

class TlvDeWorkbench extends DeWorkbench
{
    public static function getMainInstance(): LiteralWorkbench
    {
        static $instance;

        return $instance ?? ($instance = new self());
    }

    private $serializerFlags_;

    public function __construct(
        ?Schema $schema = null,
        ?RdfLiteralFactory $rdfLiteralFactory = null,
        ?int $serializerFlags = null
    ) {
        parent::__construct($schema, $rdfLiteralFactory);

        $this->serializerFlags_ = (int)$serializerFlags;
    }

    public function getSerializerFlags(): int
    {
        return $this->serializerFlags_;
    }

    public function createTlvDataElementFromTagAndProps(
        $tag,
        $props
    ): AbstractTlvDataElement {
        if (!($tag instanceof Tag)) {
            $tag = Tag::newFromHex($tag);
        }

        $props = (object)$props;

        $dataElement = $this->createDataElementFromXName(
            $props->datatypeXName,
            $props->rdfaData ?? null
        );

        if ($tag->isConstructed()) {
            return new ConstructedTlvDataElement($tag, $dataElement);
        } else {
            $props->serializer['datatypeXName'] = $props->datatypeXName;

            if (isset($props->serializer['flags'])) {
                $props->serializer['flags'] |= $this->serializerFlags_;
            } else {
                $props->serializer['flags'] = $this->serializerFlags_;
            }

            return new PrimitiveTlvDataElement(
                $tag,
                $dataElement,
                AbstractSerializer::createFromProps($props->serializer),
                $props->format ?? null
            );
        }
    }
}
