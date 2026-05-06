<?php

namespace alcamo\x690;

use alcamo\exception\{DataValidationFailed, InvalidType};

/**
 * @brief Instance of a constructed TLV data element
 *
 * @date Last reviewed 2026-05-06
 */
class ConstructedTlvDeInstance extends AbstractTlvDeInstance
{
    public static function newFromContent(
        TlvDataElement $tlvDataElement,
        iterable $content
    ): self {
        $tlvSerializer = new TlvSerializer();

        $rawContent = '';

        foreach ($content as $deInstance) {
            $rawContent .= $tlvSerializer->serializeTlvDeInstance($deInstance);
        }

        return new static($tlvDataElement, $content, $rawContent);
    }

    private $content_; ///< array of AbstractTlvDeInstance

    public function __construct(
        TlvDataElement $tlvDataElement,
        BinaryString $rawContent,
        iterable $content
    ) {
        if (!$tlvDataElement->getTag()->isConstructed()) {
            /** @throw alcamo::exception::DataValidationFailed if tag in
             *  $tlvDataElement is not constructed. */
            throw (new DataValidationFailed())->setMessageContext(
                [ 'extraMessage' => "$tag is primitive" ]
            );
        }

        $this->content_ = [];

        foreach ($content as $key => $deInstance) {
            if (!($deInstance instanceof AbstractTlvDeInstance)) {
                /** @throw alcamo::exception::InvalidType if an item in
                 *  $content is not an AbstractTlvDeInstance object. */
                throw (new InvalidType())->setMessageContext(
                    [
                        'value' => $deInstance,
                        'expectedOneOf' => AbstractTlvDeInstance::class,
                        'forKey' => $key
                    ]
                );
            }

            $this->content_[$key] = $deInstance;
        }

        parent::__construct($tlvDataElement, $rawContent);
    }

    public function getContent(): array
    {
        return $this->content_;
    }
}
