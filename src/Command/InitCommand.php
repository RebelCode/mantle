<?php

namespace RebelCode\Mantle\Command;

use RebelCode\Mantle\Project;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\OutputStyle;
use Symfony\Component\Console\Style\SymfonyStyle;

class InitCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('init');
        $this->setDescription('Create a new project');

        $this->addOption(
            'path',
            'p',
            InputOption::VALUE_OPTIONAL,
            'Specify the path to the build.json file.'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $path = $input->getOption('path');
        $jsonPath = $path ? realpath($path) : './build.json';

        $name = $io->ask('Project name', dirname(realpath($jsonPath)));
        $version = $io->ask('Version', '0.1');
        $mainFile = $io->ask('Path to main file', 'includes/main.php');

        $info = new Project\Info($name, $version, $mainFile);

        $info->slug = $io->ask('Slug', Project\Info::generateSlug($name));
        $info->shortId = $io->ask('Short code name', Project\Info::generateShortId($info->slug));
        $info->constantId = $io->ask('Constant Prefix', strtoupper($info->shortId));
        $info->description = $io->ask('Description');
        $info->url = $io->ask('Website URL');
        $info->license = $io->ask('License', 'GPL-3.0');
        $info->author = $io->ask('Author');
        $info->authorUrl = $io->ask('Author URL');
        $info->textDomain = $io->ask('Text Domain', $info->shortId);
        $info->domainPath = $io->ask('Domain Path', '/languages');
        $info->minWpVer = $io->ask('Minimum WordPress version');
        $info->minPhpVer = $io->ask('Minimum PHP version');

        $io->confirm('Do you want to add source files or directories?');

        $sources = [];

        do {
            $sources[] = $lastSource = $io->ask('Source file or directory');
        } while (strlen(trim($lastSource)) > 0 && $io->confirm('Add another source?'));

        $addInstructions = [];
        foreach ($sources as $source) {
            if (strlen(trim($source)) > 0) {
                $addInstructions[] = ['add', $source];
            }
        }

        $array = [
            'info' => $info->toArray(),
            'builds' => [
                'core' => [
                    'steps' => [
                        'Add core files' => $addInstructions,
                    ],
                ],
            ],
        ];

        $json = json_encode($array, JSON_PRETTY_PRINT);

        $io->newLine();
        $io->writeln($json);
        $io->newLine();

        if ($this->confirmCreateFile($io, $json, $jsonPath)) {
            file_put_contents($jsonPath, $json);
            $io->success('Project created successfully!');
            return 0;
        } else {
            $io->error('Project creation cancelled.');
            return 1;
        }
    }

    /** Confirms with the user whether the build.json file should be created. */
    protected function confirmCreateFile(OutputStyle $io, string $contents, string $path): bool
    {
        $io->newLine();
        $io->writeln($contents);
        $io->newLine();

        if (!$io->confirm('Does this look good?')) {
            return false;
        }

        if (file_exists($path)) {
            $fileName = basename($path);
            $article = in_array($fileName[0], ['a', 'e', 'i', 'o', 'u']) ? 'An' : 'A';

            if (!$io->confirm("{$article} \"{$fileName}\" file already exists. Do you want to overwrite it?")) {
                return false;
            }
        }

        return true;
    }
}
