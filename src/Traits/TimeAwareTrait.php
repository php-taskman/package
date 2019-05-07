<?php

namespace PhpTaskman\Package\Traits;

use PhpTaskman\Package\Services\Time;

/**
 * Trait TimeAwareTrait.
 */
trait TimeAwareTrait
{
    /**
     * @var \PhpTaskman\Package\Services\Time
     */
    protected $time;

    /**
     * @return \PhpTaskman\Package\Services\Time
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * @param \PhpTaskman\Package\Services\Time $time
     *
     * @return TimeAwareTrait
     */
    public function setTime(Time $time)
    {
        $this->time = $time;

        return $this;
    }
}
