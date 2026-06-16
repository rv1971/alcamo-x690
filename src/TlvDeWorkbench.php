<?php

namespace alcamo\x690;

use alcamo\data_element\{AbstractSerializer, DeWorkbench};
use alcamo\dom\schema\SchemaFactory;

class TlvDeWorkbench extends DeWorkbench
{
    private static $mainInstance_; ///< self

    public static function getMainInstance(): DeWorkbench
    {
        return self::$mainInstance_ ?? (
            self::$mainInstance_
                = static::newFromSchema((new SchemaFactory())->getMainSchema())
        );
    }

    public function createTlvDataElementFromTagAndProps(
        $tag,
        $props
    ): AbstractTlvDataElement {
        if (!($tag instanceof Tag)) {
            $tag = Tag::newFromHex($tag);
        }

        $props = (object)$props;

        $props->tlvDeWorkbench = $this;

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
