<?php

declare(strict_types = 1);

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
