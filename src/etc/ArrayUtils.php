<?php

namespace UWDOEM\Framework\Etc;

/**
 * Class ArrayUtils is a static class to provide array manipulation utilities
 *
 * @package UWDOEM\Framework\Etc
 */
class ArrayUtils
{

    /**
     * Disallow class instantiation
     */
    protected function __construct()
    {

    }

    /**
     * @param string|integer $needle
     * @param array          $haystack
     * @param mixed          $default
     * @return mixed
     */
    public static function findOrDefault($needle, array $haystack, $default)
    {
        return array_key_exists($needle, $haystack) ? $haystack[$needle] : $default;
    }
}
