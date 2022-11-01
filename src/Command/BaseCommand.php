<?php

namespace RebelCode\Mantle\Command;

use RebelCode\Mantle\MantleOutputStyle;
use RebelCode\Mantle\Project;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

abstract class BaseCommand extends Command
{
    /** Sets up the command */
    abstract protected function setup(): void;

    /** Runs the command. */
    abstract protected function runCommand(Project $project, MantleOutputStyle $io, InputInterface $input): int;

    /** @inheritDoc */
    protected function configure()
    {
        $this->setup();

        $this->addOption(
            'path',
            'p',
            InputOption::VALUE_OPTIONAL,
            'Specify the path to the mantle.json file.'
        );

        $this->addOption(
            'ver',
            null,
            InputOption::VALUE_REQUIRED,
            'Optional override for the version of the build'
        );
    }

    /** @inheritDoc */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new MantleOutputStyle($input, $output);

        // Get path to mantle.json file
        $inputPath = $input->getOption('path');
        $jsonPath = $inputPath ?: './mantle.json';

        // If the file doesn't exist, throw an error
        if (empty($jsonPath) || !is_readable($jsonPath)) {
            $io->error("Cannot open file at \"{$jsonPath}\"");
            return 1;
        }

        $project = Project::fromJsonFile($jsonPath);
        $project->setIo($io);

        // Set the version override, if one was provided
        $version = $input->getOption('ver') ?? null;
        if (!empty($version)) {
            $project->getInfo()->version = $version;

            foreach ($project->getBuilds() as $build) {
                $build->getInfo()->version = $version;
            }
        }

        return $this->runCommand($project, $io, $input);
    }
}
