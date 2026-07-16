<?php

namespace alcamo\x690;

use alcamo\data_element\{DeInstanceInterface, ExplainerInterface};
use alcamo\html_creation\Element;

/**
 * @brief Dumper for TLV data element instances in HTML format
 *
 * @date Last reviewed 2026-05-16
 */
class HtmlTlvDumper extends TlvDumper
{
    public const START_TAG_CONTENT_STRING = '&lt;';
    public const END_TAG_CONTENT_STRING = '&gt;';

    /**
     * @brief Default HTML parameters
     *
     * For each syntactical item, an HTML class and a prefix for the
     * `href`attribute in an `\<a>` link can be specified here.
     */
    public const DEFAULT_HTML_PARAMS = [
        'commentClass'  => null,
        'lengthClass'   => null,
        'literalClass'  => null,
        'tagClass'      => null,
        'tagHrefPrefix' => null
    ];

    private $htmlParams_; ///< object

    /**
     * @param $htmlParams object|array Parameters for html creation. See
     * alcamo::data_element::HtmlTlvDumper::DEFAULT_HTML_PARAMS. Values from
     * the latter are used for the keys not contained in $htmlParams.
     */
    public function __construct(
        ?ExplainerInterface $explainer = null,
        ?int $flags = null,
        $htmlParams = null
    ) {
        parent::__construct($explainer, $flags);

        $this->htmlParams_ =
            (object)((array)$htmlParams + static::DEFAULT_HTML_PARAMS);
    }

    public function dumpTag(AbstractTlvDeInstance $deInstance): string
    {
        return $this->createElement(
            'tag',
            parent::dumpTag($deInstance),
            $this->flags_ & self::DUMP_COMMENTS
                ? null
                : $this->explainer_
                ->getDataElementLabel($deInstance->getDataElement()),
            $deInstance->getDataElement()->getTag()
        );
    }

    public function dumpComments(
        AbstractTlvDeInstance $deInstance,
        ?string $indent = null
    ): string {
        return $this->createElement(
            'comment',
            parent::dumpComments($deInstance, $indent)
        );
    }

    public function dumpLength(AbstractTlvDeInstance $deInstance): string
    {
        return $this->createElement(
            'length',
            parent::dumpLength($deInstance),
            "decimal {$deInstance->getLength()}"
        );
    }

    public function dumpLiteral(AbstractTlvDeInstance $deInstance): string
    {
        return
            $this->createElement('literal', parent::dumpLiteral($deInstance));
    }

    protected function createElement(
        string $paramPrefix,
        string $content,
        ?string $title = null,
        ?string $hrefItem = null
    ): string {
        $classParam = "{$paramPrefix}Class";
        $hrefPrefixParam = "{$paramPrefix}HrefPrefix";

        return Element::createAOrSpan(
            $content,
            $this->htmlParams_->$classParam,
            $title,
            isset($hrefItem) && isset($this->htmlParams_->$hrefPrefixParam)
                ? $this->htmlParams_->$hrefPrefixParam . $hrefItem
                : null
        );
    }
}
