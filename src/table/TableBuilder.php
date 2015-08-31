<?php

namespace UWDOEM\Framework\Table;


use UWDOEM\Framework\Row\RowInterface;
use UWDOEM\Framework\Filter\FilterInterface;


class TableBuilder {

    /**
     * @var RowInterface[]
     */
    protected $_rows;

    /**
     * @var FilterInterface[]
     */
    protected $_filter;

    /**
     * @param RowInterface[] $rows
     * @return TableBuilder
     */
    public function setRows($rows) {
        $this->_rows = $rows;
        return $this;
    }

    /**
     * @param FilterInterface $filter
     * @return TableBuilder
     */
    public function setFilter($filter) {
        $this->_filter = $filter;
        return $this;
    }

    /**
     * @return TableBuilder
     */
    public static function begin() {
        return new static();
    }

    public function build() {
        if (!isset($this->_rows)) {
            $this->_rows = [];
        }

        if (!isset($this->_filter)) {
            $this->_filter = [];
        }
        return new Table($this->_rows, $this->_filter);
    }


}