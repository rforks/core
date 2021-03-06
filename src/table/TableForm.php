<?php

namespace Athens\Core\Table;

use Athens\Core\Filter\FilterInterface;
use Athens\Core\Filter\DummyFilter;
use Athens\Core\Form\FormTrait;
use Athens\Core\Visitor\VisitableTrait;
use Athens\Core\Row\RowInterface;
use Athens\Core\FieldBearer\FieldBearerInterface;
use Athens\Core\Form\FormAction\FormActionInterface;

/**
 * Class TableForm provides a form composed of multiple rows.
 *
 * @package Athens\Core\Table
 */
class TableForm implements TableFormInterface
{

    /** @var callable */
    protected $rowMakingFunction;

    /** @var RowInterface */
    protected $prototypicalRow;

    /** @var RowInterface[] */
    protected $rows;

    /** @var RowInterface[] */
    protected $initialRows;

    /** @var boolean */
    protected $canRemove;

    use VisitableTrait;
    use FormTrait;

    /** @return RowInterface */
    public function getPrototypicalRow()
    {
        return $this->prototypicalRow;
    }

    /**
     * @return RowInterface[]
     */
    public function getRows()
    {
        return $this->rows;
    }

    /**
     * @return FilterInterface
     */
    public function getFilter()
    {
        return new DummyFilter();
    }

    /**
     * @return FieldBearerInterface
     */
    public function getFieldBearer()
    {
        $row = $this->getPrototypicalRow();

        if ($row === null) {
            $row = $this->getRows()[0];
        }

        return $row->getFieldBearer();
    }

    /**
     * @return boolean
     */
    public function getCanRemove()
    {
        return $this->canRemove;
    }

    /**
     * @return RowInterface
     */
    protected function makeRow()
    {
        $rowMakingFunction = $this->rowMakingFunction;
        return $rowMakingFunction();
    }

    /**
     * @return string[]
     */
    protected function findRowPrefixes()
    {
        $firstPrototypicalSlug = current($this->getPrototypicalRow()->getFieldBearer()->getVisibleFields())->getSlug();

        $submittedSlugs = array_keys($_POST);

        $submittedFirstSlugMatches = array_filter(
            $submittedSlugs,
            function ($postSlug) use ($firstPrototypicalSlug) {
                return strpos($postSlug, $firstPrototypicalSlug) !== false;
            }
        );

        $rowPrefixes = array_map(
            function ($name) use ($firstPrototypicalSlug) {
                return str_replace($firstPrototypicalSlug, "", $name);
            },
            $submittedFirstSlugMatches
        );

        return $rowPrefixes;
    }

    /**
     * @return RowInterface[]
     */
    protected function makeRows()
    {
        $rows = $this->initialRows;

        if ($this->getPrototypicalRow() !== null) {
            foreach ($this->findRowPrefixes() as $prefix) {
                $newRow = $this->makeRow();

                foreach ($this->getPrototypicalRow()->getFieldBearer()->getFields() as $name => $field) {
                    $suffix = implode("-", $field->getSuffixes());

                    $newField = $newRow->getFieldBearer()->getFieldByName($name);

                    $newField->addSuffix($suffix);
                    $newField->addPrefix(trim($prefix, "-"));

                    $slug = $newField->getSlug();

                    if (array_key_exists($slug, $_POST) === true && $_POST[$slug] !== null) {
                        $newField->setInitial($_POST[$slug]);
                    }
                }
                $rows[] = $newRow;
            }
        }
        return $rows;
    }

    /**
     * @return void
     */
    protected function validate()
    {
        $this->isValid = true;

        $this->rows = $this->makeRows();

        // Validate each row indogenously
        foreach ($this->getRows() as $row) {
            foreach ($row->getFieldBearer()->getFields() as $name => $field) {
                $field->validate();
            }
        }

        // Validate each row exogenously
        foreach ($this->getRows() as $row) {
            foreach ($row->getFieldBearer()->getFields() as $name => $field) {
                if (array_key_exists($name, $this->validators) === true) {
                    foreach ($this->validators[$name] as $validator) {
                        call_user_func_array($validator, [$field, $this]);
                    }
                }
            }
        }

        // See if there exist any invalid fields
        foreach ($this->getRows() as $row) {
            foreach ($row->getFieldBearer()->getVisibleFields() as $name => $field) {
                if ($field->isValid() === false) {
                    $this->isValid = false;
                    $this->addError("Please correct the indicated errors and resubmit the form.");
                    break;
                }
            }
        }
    }

    /**
     * @param string                     $id
     * @param string[]                   $classes
     * @param array                      $data
     * @param string                     $type
     * @param string                     $method
     * @param string                     $target
     * @param RowInterface[]             $rows
     * @param callable|null              $rowMakingFunction
     * @param callable                   $onValidFunc
     * @param callable                   $onInvalidFunc
     * @param boolean                    $canRemove
     * @param FormActionInterface[]|null $actions
     * @param callable[]|null            $validators
     * @throws \Exception If rowMakingFunction is provided, but does not yield a row.
     */
    public function __construct(
        $id,
        array $classes,
        array $data,
        $type,
        $method,
        $target,
        array $rows,
        $rowMakingFunction,
        callable $onValidFunc,
        callable $onInvalidFunc,
        $canRemove,
        $actions = [],
        $validators = []
    ) {

        $this->id = $id;
        $this->type = $type;

        $this->method = $method;
        $this->target = $target;

        $this->actions = $actions;
        $this->rowMakingFunction = $rowMakingFunction;

        if (is_callable($rowMakingFunction) === true) {
            $this->prototypicalRow = $rowMakingFunction();

            if (($this->prototypicalRow instanceof RowInterface) === false) {
                $providedClass = get_class($this->prototypicalRow);
                throw new \Exception("If \$rowMakingFunction is provided and callable, then it must return" .
                    " an instance of RowInterface. Instance of $providedClass provided instead.");
            }
        } else {
            $this->prototypicalRow = null;
        }

        $this->onInvalidFunc = $onInvalidFunc;
        $this->onValidFunc = $onValidFunc;

        $this->validators = $validators;

        $this->subForms = [];

        $this->rows = $rows;
        $this->initialRows = $rows;
        $this->canRemove = $canRemove;
        $this->classes = $classes;
        $this->data = $data;
    }
}
