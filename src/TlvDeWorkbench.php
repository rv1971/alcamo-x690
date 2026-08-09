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

    public function __construct(
        ?Schema $schema = null,
        ?RdfLiteralFactory $rdfLiteralFactory = null
    ) {
        parent::__construct($schema, $rdfLiteralFactory);
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

            return new PrimitiveTlvDataElement(
                $tag,
                $dataElement,
                AbstractSerializer::createFromProps($props->serializer),
                $props->format ?? null
            );
        }
    }
}
