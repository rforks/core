<?php

namespace UWDOEM\Framework\PickA;

use UWDOEM\Framework\Etc\AbstractBuilder;
use UWDOEM\Framework\Form\FormAction\FormAction;

class PickAFormBuilder extends AbstractBuilder
{

    protected $_manifest = [];

    protected $_actions = [];


    /**
     * @param string $label
     * @return PickAFormBuilder
     */
    public function addLabel($label)
    {
        $this->_manifest[$label] = null;
        return $this;
    }

    /**
     * @param \UWDOEM\Framework\Form\FormInterface[] $forms
     * @return PickAFormBuilder
     */
    public function addForms(array $forms)
    {
        $this->_manifest = array_merge($this->_manifest, $forms);
        return $this;
    }

    /**
     * @param FormAction[] $actions
     * @return \UWDOEM\Framework\Form\FormBuilder
     */
    public function setActions($actions)
    {
        $this->_actions = $actions;
        return $this;
    }

    /**
     * @return PickAFormInterface
     */
    public function build()
    {
        $this->validateId();

        return new PickAForm($this->_id, $this->_manifest, $this->_actions);
    }
}
