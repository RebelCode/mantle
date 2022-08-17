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
            'Specify the path to the mantle.json file.'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $path = $input->getOption('path');
        $jsonPath = $path ? realpath($path) : './mantle.json';

        $dirName = basename(dirname(realpath($jsonPath)));
        $projectName = implode(' ', array_map('ucfirst', preg_split('/[-_]/', $dirName)));

        $name = $io->ask('Project name', $projectName);
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

        if ($io->confirm('Do you want to generate a WordPress.org readme file?')) {
            $info->wpOrg = new Project\WpOrgInfo();
            $info->wpOrg->name = $io->ask('Plugin name', $info->name);
            $info->wpOrg->slug = $io->ask('Plugin URL slug', Project\Info::generateSlug($info->wpOrg->name));
            $info->wpOrg->testedUpTo = $io->ask('Latest tested WP version');

            $tags = $io->ask('Tags (separate by a comma)');
            $contributors = $io->ask('Contributors (separate by a comma)');

            $info->wpOrg->tags = array_map('trim', explode(',', $tags));
            $info->wpOrg->contributors = array_map('trim', explode(',', $contributors));
        }

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

        if ($this->confirmCreateFile($io, $json, $jsonPath)) {
            file_put_contents($jsonPath, $json);
            $io->success('Project created successfully!');
            return 0;
        } else {
            $io->error('Project creation cancelled.');
            return 1;
        }
    }

    /** Confirms with the user whether the mantle.json file should be created. */
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
