<?php

namespace PhpTaskman\Package\Services;

/**
 * Class Time.
 */
class Time
{
    /**
     * @return int
     */
    public function getTimestamp()
    {
        return \time();
    }
}
