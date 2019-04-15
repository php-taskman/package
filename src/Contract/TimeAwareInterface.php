<?php

declare(strict_types = 1);

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
     * @param \OpenEuropa\TaskRunner\Services\Time $time
     *
     * @return $this
     */
    public function setTime(Time $time);
}
