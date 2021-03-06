<?php

namespace Athens\Core\PickA;

use Athens\Core\Writable\WritableInterface;

interface PickAInterface extends WritableInterface
{

    /**
     * @return array
     */
    public function getManifest();

    /**
     * @return string[]
     */
    public function getLabels();

    /**
     * @return WritableInterface[]
     */
    public function getWritables();
}
