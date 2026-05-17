<?php

namespace alcamo\x690;

use alcamo\data_element\{DeInstanceInterface, ExplainerInterface};

/**
 * @brief Dumper for TLV data element instances
 *
 * @date Last reviewed 2026-05-07
 */
class TlvDumper
{
    public const DUMP_COMMENTS = 1; ///< Include comments in dump
    public const DUMP_LENGTH   = 2; ///< Include actual length in dump

    public const INDENT_STRING = '  '; /// Indentation for sub-objects

    protected $explainer_; ///< ?ExplainerInterface
    protected $flags_;     ///< int

    public function __construct(
        ?ExplainerInterface $explainer = null,
        ?int $flags = null
    ) {
        $this->explainer_ = $explainer;
        $this->flags_ = (int)$flags;
    }

    public function getExplainer(): ExplainerInterface
    {
        return $this->explainer_;
    }

    public function getFlags(): int
    {
        return $this->flags_;
    }

    public function dumpTag(AbstractTlvDeInstance $deInstance): string
    {
        return $deInstance->getDataElement()->getTag();
    }

    public function dumpComments(
        AbstractTlvDeInstance $deInstance,
        ?string $indent = null
    ): string {
        return $deInstance instanceof DeInstanceInterface
            ? ($this->explainer_->explainAsMarkdownText($deInstance)
               ->indent("$indent; "))
            : ("$indent; " . $this->explainer_
               ->getDataElementLabel($deInstance->getDataElement()));
    }

    public function dumpLength(AbstractTlvDeInstance $deInstance): string
    {
        return sprintf('[%X]', $deInstance->getLength());
    }

    public function dumpLiteral(AbstractTlvDeInstance $deInstance): string
    {
        return $deInstance->getDataElement()->getSerializer()
            ->dump($deInstance->getLiteral());
    }

    public function dumpTlvDeInstance(
        AbstractTlvDeInstance $deInstance,
        ?string $indent = null
    ): string {
        $result = '';

        if ($this->flags_ & self::DUMP_COMMENTS) {
            $result .= $this->dumpComments($deInstance, $indent) . "\n";
        }

        $result .= $this->dumpTag($deInstance);

        if ($this->flags_ & self::DUMP_LENGTH) {
            $result .= ' ' . $this->dumpLength($deInstance);
        }

        $result .= ' <';

        if ($deInstance instanceof PrimitiveTlvDeInstance) {
            $result .= ' ' . $this->dumpLiteral($deInstance) . ' ';
        } else {
            if (
                count($deInstance->getChildren()) == 1
                    && !($this->flags_ & self::DUMP_COMMENTS)
            ) {
                $result .= ' '
                    . $this->dumpTlvDeInstance($deInstance->getChildren()[0])
                    . ' ';
            } else {
                $result .= "\n"
                    . $this->dumpTlvDeInstances(
                        $deInstance->getChildren(),
                        static::INDENT_STRING . $indent
                    )
                    . "\n";
            }
        }

        $result .= ">\n";
    }

    public function dumpTlvDeInstances(
        iterable $deInstances,
        ?string $indent = null
    ): string {
        $result = '';

        foreach ($deInstances as $deInstance) {
            $result .= $this->dumpTlvDeInstance($deInstance, $indent);
        }

        return $result;
    }
}
