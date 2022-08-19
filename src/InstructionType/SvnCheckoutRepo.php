<?php

namespace RebelCode\Mantle\InstructionType;

use RebelCode\Mantle\InstructionType;
use RebelCode\Mantle\MantleOutputStyle;
use RebelCode\Mantle\Project;
use RebelCode\Mantle\Svn\SvnRepo;

/**
 * Clones a WordPress.org SVN repository.
 *
 * This instruction type is used internally as part of the publishing process.
 */
class SvnCheckoutRepo implements InstructionType
{
    /** @var SvnRepo */
    protected $repo;

    /**
     * Constructor.
     *
     * @param SvnRepo $repo The repository to checkout.
     */
    public function __construct(SvnRepo $repo)
    {
        $this->repo = $repo;
    }

    /** {@inheritdoc} */
    public function run(Project\Build $build, array $args, MantleOutputStyle $io): void
    {
        $io->writeInstruction('cyan', '[SVN] Cloning %s', $this->repo->getRemoteUrl());
        $this->repo->clear();
        $this->repo->checkout('', SvnRepo::DEPTH_EMPTY);
        $this->repo->pull('trunk', SvnRepo::DEPTH_INFINITY);
        $this->repo->pull('tags', SvnRepo::DEPTH_IMMEDIATES);
    }
}
