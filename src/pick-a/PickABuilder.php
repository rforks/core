<?php

namespace Athens\Core\PickA;

use Athens\Core\Writable\AbstractWritableBuilder;

/**
 * Class PickABuilder
 *
 * @package Athens\Core\PickA
 */
class PickABuilder extends AbstractWritableBuilder
{

    /** @var array */
    protected $manifest = [];

    /**
     * @return PickA
     */
    public function build()
    {
        $this->validateId();

        return new PickA($this->id, $this->classes, $this->data, $this->manifest);
    }

    /**
     * @param string $label
     * @return PickABuilder
     */
    public function addLabel($label)
    {
        $this->manifest[$label] = null;
        return $this;
    }

    /**
     * @param \Athens\Core\Writable\WritableInterface[] $writables
     * @return PickABuilder
     */
    public function addWritables(array $writables)
    {
        $this->manifest = array_merge($this->manifest, $writables);
        return $this;
    }
}
