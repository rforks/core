<?php

namespace UWDOEM\Framework\Etc;

/**
 * Class AbstractBuilder is a parent class for all Builder classes.
 *
 * @package UWDOEM\Framework\Etc
 */
abstract class AbstractBuilder
{

    /** @var string */
    protected $_id;

    /** @return static */
    public static function begin()
    {
        return new static();
    }

    /** @return $this */
    public function clear()
    {
        return new static();
    }

    /**
     * @param string $id
     * @return $this
     */
    public function setId($id)
    {
        $this->_id = $id;
        return $this;
    }

    protected function validateId()
    {
        if (!isset($this->_id)) {
            throw new \RuntimeException("Must use ::setId to provide a form id before calling this method.");
        }
    }

    /**
     * Returns an instance of the object type under construction.
     */
    abstract public function build();
}
