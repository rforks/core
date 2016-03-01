<?php

namespace Athens\Core\Section;

use Athens\Core\Etc\AbstractBuilder;
use Athens\Core\Etc\SafeString;
use Athens\Core\Field\Field;
use Athens\Core\Field\FieldBuilder;
use Athens\Core\Writer\WritableInterface;

/**
 * Class SectionBuilder
 *
 * @package Athens\Core\Section
 */
class SectionBuilder extends AbstractBuilder
{

    /** @var string */
    protected $type = "base";

    /** @var WritableInterface[] */
    protected $writables = [];

    /**
     * @param string $label
     * @return SectionBuilder
     */
    public function addLabel($label)
    {
        $label = FieldBuilder::begin()
            ->setType(Field::FIELD_TYPE_SECTION_LABEL)
            ->setLabel($label)
            ->setInitial($label)
            ->build();

        $this->addWritable($label);
        return $this;
    }

    /**
     * @param string $content
     * @return SectionBuilder
     */
    public function addContent($content)
    {
        if (($content instanceof SafeString) === false) {
            $content = htmlentities($content);
        }
        $content = SafeString::fromString(nl2br($content));

        return $this->addLiteralContent($content);
    }

    /**
     * @param string $content
     * @return SectionBuilder
     */
    public function addLiteralContent($content)
    {
        $content = FieldBuilder::begin()
            ->setType(Field::FIELD_TYPE_LITERAL)
            ->setLabel("section-content")
            ->setInitial($content)
            ->build();

        $this->addWritable($content);

        return $this;
    }

    /**
     * @param string $type
     * @return SectionBuilder
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @param WritableInterface $writable
     * @return SectionBuilder
     */
    public function addWritable(WritableInterface $writable)
    {
        $this->writables[] = $writable;
        return $this;
    }

    /**
     * @return SectionInterface
     */
    public function build()
    {
        $this->validateId();

        return new Section($this->id, $this->classes, $this->writables, $this->type);
    }
}
