<?php

namespace alcamo\x690;

use alcamo\data_element\{DeInstanceInterface, ExplainerInterface};

/**
 * @brief Dumper for TLV data element instances in HTML format
 *
 * @date Last reviewed 2026-05-16
 */
class HtmlTlvDumper extends TlvDumper
{
    /**
     * @brief Default HTML parameters
     *
     * For each syntactical item, an HTML class and a prefix for the
     * `href`attribute in an `\<a>` link can be specified.
     */
    public const DEFAULT_HTML_PARAMS = [
        'commentsClass' => null,
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
            parent::dumpTag($deInstance),
            'tag',
            $this->flags_ & self::DUMP_COMMENTS
                ? null
                : parent::dumpComments($deInstance),
            $deInstance->getDataElement()->getTag()
        );
    }

    public function dumpComments(
        AbstractTlvDeInstance $deInstance,
        ?string $indent = null
    ): string {
        return $this->createElement(
            parent::dumpComments($deInstance, $indent),
            'comments'
        );
    }

    public function dumpLength(AbstractTlvDeInstance $deInstance): string
    {
        return $this->createElement(
            parent::dumpLength($deInstance),
            'length',
            "decimal {$deInstance->getLength()}"
        );
    }

    public function dumpLiteral(AbstractTlvDeInstance $deInstance): string
    {
        return
            $this->createElement(parent::dumpLiteral($deInstance), 'literal');
    }

    protected function createElement(
        string $content,
        string $paramPrefix,
        ?string $title = null,
        ?string $hrefItem = null
    ): string {
        $classParam = "{$paramPrefix}Class";
        $hrefPrefixParam = "{$paramPrefix}HrefPrefix";

        return Element::createHtml(
            $content,
            $this->htmlParams_->$classParam,
            $title,
            isset($hrefItem) && isset($this->htmlParams_->$hrefPrefixParam)
                ? $this->htmlParams_->$hrefPrefixParam . $hrefItem
                : null
        );
    }
}
