<?php

namespace RebelCode\Mantle\InstructionType;

use RebelCode\Mantle\InstructionType;
use RebelCode\Mantle\MantleOutputStyle;
use RebelCode\Mantle\Project;
use RebelCode\Mantle\Svn\SvnRepo;
use RebelCode\Mantle\Svn\SvnStatus;
use RebelCode\Mantle\Svn\SvnStatusEntry;
use RebelCode\Mantle\Utils;

/**
 * Updates the trunk of the WordPress.org SVN repository with the contents of a build.
 *
 * This instruction type is used internally as part of the publishing process.
 */
class SvnUpdateTrunk implements InstructionType
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

        $io->writeInstruction('cyan', '[SVN] %s', 'Replacing trunk with build');

        // Replace trunk with build
        $trunkDir = $this->repo->getLocalDirectory() . '/trunk';
        Utils::rmDirRecursive($trunkDir);
        Utils::copyDirRecursive($project->getConfig()->buildDir, $trunkDir);
        $this->repo->update('trunk');

        // Get new status of local copy
        $status = $this->repo->status('trunk');
        $hasChanges = count($status->getEntries()) > 0;

        if (!$hasChanges) {
            if (!$io->confirm('There are no changes to commit in the trunk. Do you want to publish anyway?')) {
                $io->error('Aborted');
                exit(1);
            }
        } else {
            $this->outputChanges($status, $io);

            $message = $project->getConfig()->getSvnTrunkCommitMsg($build);

            $io->writeInstruction('cyan', '[SVN] Commit "%s"...', $message);
            $this->repo->commit($message);
        }
    }

    /** Outputs the changes in an SvnStatus object. */
    protected function outputChanges(SvnStatus $status, MantleOutputStyle $io): void
    {
        if ($io->isVeryVerbose()) {
            foreach ($status->getEntries() as $entry) {
                switch ($entry->getType()) {
                    case SvnStatusEntry::ADDED:
                        $io->writeInstruction('cyan', '[SVN] Added %s', $entry->getPath());
                        break;
                    case SvnStatusEntry::DELETED:
                        $io->writeInstruction('cyan', '[SVN] Deleted %s', $entry->getPath());
                        break;
                }
            }
        } else {
            $io->writeInstruction('green', '[SVN] %s files added', $status->getCount(SvnStatusEntry::ADDED));
            $io->writeInstruction('red', '[SVN] %s files removed', $status->getCount(SvnStatusEntry::DELETED));
            $io->writeInstruction('yellow', '[SVN] %s files changed', $status->getCount(SvnStatusEntry::MODIFIED));
        }
    }
}
