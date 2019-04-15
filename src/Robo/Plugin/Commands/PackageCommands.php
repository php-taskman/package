<?php

declare(strict_types = 1);

namespace PhpTaskman\Package\Robo\Plugin\Commands;

use PhpTaskman\Core\Robo\Plugin\Commands\AbstractCommands;
use PhpTaskman\Core\Taskman;
use PhpTaskman\CoreTasks\Plugin\Task\CollectionFactoryTask;
use PhpTaskman\Package\Contract\RepositoryAwareInterface;
use PhpTaskman\Package\Contract\TimeAwareInterface;
use PhpTaskman\Package\Services\Time;
use PhpTaskman\Package\Traits\RepositoryAwareTrait;
use PhpTaskman\Package\Traits\TimeAwareTrait;
use Gitonomy\Git\Reference\Branch;
use Gitonomy\Git\Repository;
use Robo\Common\ResourceExistenceChecker;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Input\InputOption;

class PackageCommands extends AbstractCommands implements
    RepositoryAwareInterface,
    TimeAwareInterface
{
    use RepositoryAwareTrait;
    use TimeAwareTrait;
    use ResourceExistenceChecker;

    /**
     * Create a release for the current project.
     *
     * This command creates a .tag.gz archive for the current project named as
     * follow:
     *
     * [PROJECT-NAME]-[CURRENT-TAG].tar.gz
     *
     * If the current commit is not tagged then the current local branch name will
     * be used:
     *
     * [PROJECT-NAME]-[BRANCH-NAME].tar.gz
     *
     * When running the release command will create a temporary release directory
     * named after the project itself. Such a directory will be deleted after
     * the project archive is created.
     *
     * If you wish to keep the directory use the "--keep" option.
     *
     * If you wish to override the current tag use the "--tag" option.
     *
     * Before the release directory is archived you can run a list of packaging
     * commands in your runner.yml.dist, as shown below:
     *
     * > release:
     * >   tasks:
     * >     - { task: "copy", from: "css",    to: "my-project/css" }
     * >     - { task: "copy", from: "fonts",  to: "my-project/fonts" }
     * >     - { task: "copy", from: "images", to: "my-project/images" }
     *
     * @param array $options
     *   Command options.
     *
     * @return \Robo\Collection\CollectionBuilder
     *   Collection builder.
     *
     * @command package:create-archive
     *
     * @option tag  Release tag, will override current repository tag.
     * @option keep Whereas to keep the temporary release directory or not.
     *
     * @aliases package:ca,pca
     */
    public function createRelease(array $options = [
        'tag' => InputOption::VALUE_OPTIONAL,
        'keep' => false,
    ])
    {
        $this->checkResource('composer.json', 'file');

        $composerFile = \realpath('composer.json');
        $composerConfig = Taskman::createJsonConfiguration([$composerFile]);

        $name = explode('/', $composerConfig->get('name'), 2)[1];
        $version = $options['tag'] ?? $this->getVersionString();
        $archive = sprintf('%s-%s.tar.gz', $name, $version);

        $tasks = [
            // Make sure we do not have a release directory yet.
            $this->taskFilesystemStack()->remove([$archive, $name]),

            // Get non-modified code using git archive.
            $this->taskGitStack()->exec(['archive', 'HEAD', '-o ' . $name . '.zip']),
            $this->taskExtract($name . '.zip')->to($name),
            $this->taskFilesystemStack()->remove($name . '.zip'),
        ];

        // Append release tasks defined in runner.yml.dist.
        $releaseTasks = [
            'tasks' => $this->getConfig()->get('package.tasks')
        ];
        $tasks[] = $this->task(CollectionFactoryTask::class)->setTaskArguments($releaseTasks);

        // Create archive.
        $tasks[] = $this->taskExecStack()->exec('tar -czf ' . $archive . ' ' . $name);

        // Remove release directory, if not specified otherwise.
        if (!$options['keep']) {
            $tasks[] = $this->taskFilesystemStack()->remove($name);
        }

        return $this->collectionBuilder()->addTaskList($tasks);
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFile(): string
    {
        return __DIR__ . '/../../../../config/commands/package.yml';
    }

    public function getDefaultConfigurationFile(): string
    {
        return __DIR__ . '/../../../../config/default.yml';
    }

    /**
     * Set runtime configuration values.
     *
     * @param \Symfony\Component\Console\Event\ConsoleCommandEvent $event
     *
     * @hook command-event release:create-archive
     */
    public function setRuntimeConfig(ConsoleCommandEvent $event)
    {
        // Todo: Find a better way to do this.
        $this->setTime(new Time());
        $this->setRepository(new Repository(\getcwd()));

        $timeFormat = $this->getConfig()->get('package.time_format');
        $dateFormat = $this->getConfig()->get('package.date_format');
        $timestamp = $this->getTime()->getTimestamp();

        $this->getConfig()->set('package.version', $this->getVersionString());
        $this->getConfig()->set('package.date', \date($dateFormat, $timestamp));
        $this->getConfig()->set('package.time', \date($timeFormat, $timestamp));
        $this->getConfig()->set('package.timestamp', $timestamp);
    }

    /**
     * Return version string for current HEAD: either a tag or local branch name.
     *
     * @return string
     *   Tag name or empty string if none set.
     */
    private function getVersionString()
    {
        $this->setRepository(new Repository($this->getConfig()->get('taskman.working_dir')));

        $repository = $this->getRepository();
        $revision = $repository->getHead()->getRevision();

        // Get current commit hash.
        $hash = $repository->getHead()->getCommit()->getHash();

        // Resolve tags for current HEAD.
        // In case of multiple tags per commit take the latest one.
        $tags = $repository->getReferences()->resolveTags($hash);
        $tag = \end($tags);

        // Resolve local branch name for current HEAD.
        $branches = \array_filter(
            $repository->getReferences()->getBranches(),
            static function (Branch $branch) use ($revision) {
                return $branch->isLocal() && $branch->getRevision() === $revision;
            }
        );
        $branch = \reset($branches);

        // Make sure we always have a version string, i.e. when in detached state.
        $version = $hash;

        // If HEAD is attached use branch name.
        if (false !== $branch) {
            $version = $branch->getName();
        }

        // Current commit is tagged, prefer tag.
        if (false !== $tag) {
            $version = $tag->getName();
        }

        return $version;
    }
}
