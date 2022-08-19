<?php

namespace RebelCode\Mantle\InstructionType;

use RebelCode\Mantle\InstructionType;
use RebelCode\Mantle\MantleOutputStyle;
use RebelCode\Mantle\Project;
use RebelCode\Mantle\Svn\SvnRepo;

/**
 * Creates a new tag from the trunk in an SVN repository.
 *
 * This instruction type is used internally as part of the publishing process.
 */
class SvnCreateTag implements InstructionType
{
    /** @var SvnRepo */
    protected $repo;

    /**
     * Constructor.
     *
     * @param SvnRepo $repo The repository.
     */
    public function __construct(SvnRepo $repo)
    {
        $this->repo = $repo;
    }

    /** {@inheritdoc} */
    public function run(Project\Build $build, array $args, MantleOutputStyle $io): void
    {
        $project = $build->getProject();
        $tagName = $project->getInfo()->version;
        $message = $project->getConfig()->getSvnTagCommitMsg($build);

        $io->writeInstruction('cyan', '[SVN] Committing "%s"...', $message);
        $this->repo->createTag($tagName, $message);
    }
}
