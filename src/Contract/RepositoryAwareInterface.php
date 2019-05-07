<?php

namespace PhpTaskman\Package\Contract;

use Gitonomy\Git\Repository;

/**
 * Interface RepositoryAwareInterface.
 */
interface RepositoryAwareInterface
{
    /**
     * @return Repository
     */
    public function getRepository();

    /**
     * @param \Gitonomy\Git\Repository $repository
     *
     * @return $this
     */
    public function setRepository(Repository $repository);
}
