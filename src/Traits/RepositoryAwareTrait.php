<?php

namespace PhpTaskman\Package\Traits;

use Gitonomy\Git\Repository;

/**
 * Trait RepositoryAwareTrait.
 */
trait RepositoryAwareTrait
{
    /**
     * @var \Gitonomy\Git\Repository
     */
    protected $repository;

    /**
     * @return \Gitonomy\Git\Repository
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * @param \Gitonomy\Git\Repository $repository
     *
     * @return $this
     */
    public function setRepository(Repository $repository)
    {
        $this->repository = $repository;

        return $this;
    }
}
