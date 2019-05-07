<?php

namespace PhpTaskman\Package\Contract;

use PhpTaskman\Package\Services\Time;

/**
 * Interface TimeAwareInterface.
 */
interface TimeAwareInterface
{
    /**
     * @return $this
     */
    public function getTime();

    /**
     * @param \PhpTaskman\Package\Services\Time $time
     *
     * @return $this
     */
    public function setTime(Time $time);
}
