<?php

namespace RebelCode\Mantle\Command;

use RebelCode\Mantle\MantleOutputStyle;
use RebelCode\Mantle\Project;
use Symfony\Component\Console\Input\InputInterface;

class CleanCommand extends BaseCommand
{
    /** @inheritDoc */
    protected function setup(): void
    {
        $this->setName('clean');
        $this->setDescription('Removes any temporary build files that were generated');
    }

    /** @inheritDoc */
    protected function runCommand(Project $project, MantleOutputStyle $io, InputInterface $input): int
    {
        $project->clean();
        $io->writeln('<fg=green>Build complete</>');

        return 0;
    }
}
