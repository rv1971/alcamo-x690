<?php

namespace alcamo\x690;

use alcamo\data_element\DeWorkbench;
use alcamo\rdf_literal_workbench\LiteralWorkbench;
use alcamo\rdf_literal_serializer\AbstractSerializer;
use alcamo\dom\schema\SchemaFactory;

class TlvDeWorkbench extends DeWorkbench
{
    public static function getMainInstance(): LiteralWorkbench
    {
        static $instance;

        return $instance ??
            ($instance =
             self::newFromSchema((new SchemaFactory())->getMainSchema()));
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
