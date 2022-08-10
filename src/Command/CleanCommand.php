<?php

namespace RebelCode\Mantle\Command;

use RebelCode\Mantle\Project;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\OutputStyle;
use Symfony\Component\Console\Style\SymfonyStyle;

class CleanCommand extends BaseCommand
{
    /** @inheritDoc */
    protected function setup(): void
    {
        $this->setName('clean');
        $this->setDescription('Removes any temporary build files that were generated');
    }

    /** @inheritDoc */
    protected function runCommand(Project $project, OutputStyle $io, InputInterface $input): int
    {
        $project->clean();
        $io->writeln('<fg=green>Build complete</>');

        return 0;
    }
}
