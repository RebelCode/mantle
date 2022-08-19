<?php

namespace RebelCode\Mantle\InstructionType;

use RebelCode\Mantle\InstructionType;
use RebelCode\Mantle\MantleOutputStyle;
use RebelCode\Mantle\Project;
use RebelCode\Mantle\Svn\SvnRepo;

/**
 * Checks if a tag already exists in the SVN repository, and warns the user if it does.
 *
 * This instruction type is used internally as part of the publishing process.
 */
class SvnCheckTagAlreadyExists implements InstructionType
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
        $tagDir = $this->repo->getLocalDirectory() . '/tags/' . $tagName;

        if (file_exists($tagDir) && is_dir($tagDir)) {
            if (!$io->confirm("Tag <fg=cyan>{$tagName}</>already exists. Do you want to overwrite it?", false)) {
                $io->error('Aborted');
                exit(1);
            }
        }
    }
}
