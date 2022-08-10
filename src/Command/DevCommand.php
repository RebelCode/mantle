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

class DevCommand extends BaseCommand
{
    protected function setup(): void
    {
        $this->setName('dev');
        $this->setDescription('Generate the plugin files needed for development');

        $this->addArgument(
            'build',
            InputArgument::OPTIONAL,
            'The build to use to generate the development files. Omit to use the build specified in the project\'s config.'
        );
    }

    protected function runCommand(Project $project, OutputStyle $io, InputInterface $input): int
    {
        $buildName = $input->getArgument('build') ?? $project->getConfig()->devBuildName;

        if (empty($buildName)) {
            $io->error(
                'No development build is specified. Set the "config.devBuild" property in the project config or specify a build name as argument.'
            );
            return 1;
        }

        // Make sure the build exists
        $build = $project->getBuild($buildName);
        if ($build === null) {
            $io->error("Build \"{$buildName}\" does not exist");
            return 1;
        }

        $devProject = $project->getForDevelopment();
        $devProject->build($devProject->devBuild($buildName));

        $gitIgnoreFile = $project->getPath() . '/.gitignore';

        if (is_readable($gitIgnoreFile)) {
            $fileToIgnore = '/' . $project->getInfo()->slug . '.php';
            $ignoreContents = file_get_contents($gitIgnoreFile);

            if (!preg_match('#^' . preg_quote($fileToIgnore) . '$#m', $ignoreContents)) {
                if ($io->confirm("Do you want to add {$fileToIgnore} to the .gitignore file?")) {
                    $newContents = rtrim($ignoreContents) . "\n" . $fileToIgnore . "\n";
                    file_put_contents($gitIgnoreFile, $newContents);
                }
            }
        }

        return 0;
    }
}
