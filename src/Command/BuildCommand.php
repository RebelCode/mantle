<?php

namespace RebelCode\Mantle\Command;

use RebelCode\Mantle\MantleOutputStyle;
use RebelCode\Mantle\Project;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;

class BuildCommand extends BaseCommand
{
    /** @inheritDoc */
    protected function setup(): void
    {
        $this->setName('build');
        $this->setDescription('Build the plugin');

        $this->addArgument(
            'builds',
            InputArgument::IS_ARRAY,
            'The builds to run. Omit to create all the builds.'
        );
    }

    /** @inheritDoc */
    protected function runCommand(Project $project, MantleOutputStyle $io, InputInterface $input): int
    {
        // If no builds were specified, run all.
        $builds = $input->getArgument('builds') ?? [];
        $builds = empty($builds)
            ? array_map(function (Project\Build $build) {
                return $build->getName();
            }, $project->getBuilds())
            : $builds;

        // Make sure all the builds exist before running them, so we don't fail mid-build
        foreach ($builds as $buildName) {
            if ($project->getBuild($buildName) === null) {
                $io->error("Build \"{$buildName}\" does not exist");
                return 1;
            }
        }

        $io->writeln("<fg=green>=> Building {$project->getInfo()->version}</>");

        // Run them
        foreach ($builds as $buildName) {
            $project->clean();
            $project->build($buildName);
            $project->zip($buildName);
        }

        $io->writeln('<fg=green>=> Build complete</>');

        return 0;
    }
}
