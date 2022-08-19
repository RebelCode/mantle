<?php

namespace RebelCode\Mantle;

use InvalidArgumentException;
use RebelCode\Mantle\InstructionType\GenerateInstructionType;
use RebelCode\Mantle\InstructionType\ReadmeInstructionType;
use RebelCode\Mantle\Project\Build;
use RebelCode\Mantle\Project\Config;
use RebelCode\Mantle\Project\Info;
use RebelCode\Mantle\Project\Instruction;
use RebelCode\Mantle\Project\Step;
use RuntimeException;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;

class Project
{
    /** @var string The path to the project's root directory. */
    protected $path;
    /** @var Info The root project info. */
    protected $info;
    /** @var Config The config. */
    protected $config;
    /** @var array<string,Build> The builds. */
    protected $builds;
    /** @var MantleOutputStyle The IO, used to output progress. */
    protected $io;

    /**
     * Constructor.
     *
     * @param string $path The path to the project directory.
     * @param Config $config The project's configuration.
     * @param Info $info Information about the project.
     * @param Build[] $builds An array of builds.
     */
    public function __construct(string $path, Config $config, Info $info, array $builds = [])
    {
        $this->setPath($path);
        $this->info = $info;
        $this->config = $config;
        $this->builds = $builds;
        $this->io = new MantleOutputStyle(new StringInput(''), new NullOutput());

        return $this;
    }

    /** Retrieves the project's path. */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Retrieves the path to the main PHP file. This file is generated by Mantle before building and during
     * development.
     */
    public function getMainFilePath(): string
    {
        return $this->path . '/' . $this->info->slug . '.php';
    }

    /** Retrieves the path to the directory that contains the readme files, or null if the directory does not exist. */
    public function getReadmeDirPath(): ?string
    {
        $path = $this->path . '/_plugin/readme';
        return is_dir($path) ? $path : null;
    }

    /** Retrieves the path to the plugin main file template, or null if the file does not exist. */
    public function getMainFileTemplatePath(): ?string
    {
        $customPath = $this->path . '/_plugin/plugin.php.template';

        return !is_readable($customPath) || is_dir($customPath)
            ? realpath(__DIR__ . '/../templates/plugin.php.template')
            : $customPath;
    }

    /** Retrieves the path to the project's changelog file. */
    public function getChangelogPath(): ?string
    {
        $searchPaths = [
            $this->path . '/CHANGELOG.md',
            $this->path . '/CHANGELOG.MD',
            $this->path . '/changelog.md',
            $this->path . '/changelog.MD',
            $this->path . '/CHANGELOG',
        ];

        foreach ($searchPaths as $filePath) {
            if (is_readable($filePath) && !is_dir($filePath)) {
                return $filePath;
            }
        }

        return null;
    }

    /** Sets the project's path. */
    public function setPath(string $path): void
    {
        $this->path = rtrim($path, '\\/');
    }

    /** Retrieves the top-level information about the project. */
    public function getInfo(): Info
    {
        return $this->info;
    }

    /** Sets the project's top-level information. */
    public function setInfo(Info $info): void
    {
        $this->info = $info;
    }

    /** Retrieves the project's configuration. */
    public function getConfig(): Config
    {
        return $this->config;
    }

    /** Sets the project's configuration. */
    public function setConfig(Config $config): void
    {
        $this->config = $config;
    }

    /** Retrieves the project's IO. */
    public function getIo(): MantleOutputStyle
    {
        return $this->io;
    }

    /** Sets the project's IO. */
    public function setIo(?MantleOutputStyle $io): void
    {
        $this->io = $io;
    }

    /**
     * Retrieves the project's builds.
     *
     * @return Build[]
     */
    public function getBuilds(): array
    {
        return $this->builds;
    }

    /**
     * Retrieves a build by its name.
     *
     * @return Build|null The build, or null if not found.
     */
    public function getBuild(string $name): ?Build
    {
        $idx = $this->searchForBuild($name);
        return $idx >= 0 ? $this->builds[$idx] : null;
    }

    /** Sets the project's builds. */
    public function setBuilds(array $builds): void
    {
        $this->builds = [];
        foreach ($builds as $build) {
            $this->addBuild($build);
        }
    }

    /** Adds a build to the project. If a build already exists with the same name, it will be overridden. */
    public function addBuild(Build $build): void
    {
        $idx = $this->searchForBuild($build->getName());

        if ($idx >= 0) {
            $this->builds[$idx] = $build;
        } else {
            $this->builds[] = $build;
        }
    }

    /** Creates a new build for this project and adds it to the project. */
    public function createBuild(string $name): Build
    {
        $build = new Build($name, $this);
        $this->addBuild($build);

        return $build;
    }

    /** Searches for the index of a build, by its name. */
    protected function searchForBuild(string $name): int
    {
        foreach ($this->builds as $idx => $build) {
            if (strtolower($build->getName()) === strtolower($name)) {
                return $idx;
            }
        }

        return -1;
    }

    /** Cleans the project before a build. */
    public function clean(): void
    {
        if (file_exists($this->config->buildDir)) {
            if ($this->io->isVeryVerbose()) {
                $this->io->topLevel('Cleaning project');
            }

            Utils::rmDirRecursive($this->config->buildDir);
        }

        $mainFile = $this->getMainFilePath();
        if (file_exists($mainFile)) {
            unlink($mainFile);
        }
    }

    /** Runs a specific build, with an optional list of steps. If omitted, all steps are run. */
    public function build(string $buildName, array $steps = []): void
    {
        $build = $this->getBuild($buildName);
        if ($build === null) {
            throw new InvalidArgumentException("Build \"{$buildName}\" is not defined in this project");
        }

        Utils::ensurePathExists($this->config->buildDir);

        $this->io->topLevel("Preparing <fg=cyan>{$build->getName()}</>");
        $this->getPreBuild()->run($build);
        $build->run($steps);
    }

    /** Packages a build into a zip file. */
    public function zip(string $buildName): void
    {
        $build = $this->getBuild($buildName);
        if ($build === null) {
            throw new InvalidArgumentException("Build \"{$buildName}\" is not defined in this project");
        }

        // Create the zip file from the temp directory
        $zipFilePath = $this->path . '/' . $build->getZipFileName();
        $zipInputPath = $this->config->buildDir;
        $zipInternalFolder = $this->info->slug . '/';
        Utils::zipDirectory($zipFilePath, $zipInputPath, $zipInternalFolder);

        $this->io->topLevel("Packaged <fg=cyan>$buildName</> into <fg=blue>$zipFilePath</>");
    }

    /** Creates a copy of the project, for development purposes. */
    public function getForDevelopment(): Project
    {
        // Create a development project that uses the project's path as the temp directory.
        // This is done to generate files in the project's directory, rather than in the temp directory.
        $project = clone $this;
        $project->config = clone $this->config;
        $project->config->buildDir = $this->path;

        // Create a dev variant of each build, with no steps, using the original build's info.
        foreach ($project->builds as $ogBuild) {
            $devBuild = $project->createBuild($this->devBuild($ogBuild->getName()));
            $devBuild->setInfo($ogBuild->getInfo());
        }

        return $project;
    }

    /** Retrieves the name of the development version of a build.  */
    public function devBuild($buildName): string
    {
        return "__dev_{$buildName}__";
    }

    /** Retrieves the step that runs before any build to perform Mantle's built-in instructions. */
    public function getPreBuild(): Step
    {
        $instructions = [
            new Instruction(
                new GenerateInstructionType(),
                [
                    $this->getMainFileTemplatePath(),
                    basename($this->getMainFilePath()),
                ],
            ),
        ];

        if ($this->info->wpOrg !== null) {
            $instructions[] = new Instruction(new ReadmeInstructionType(), []);
        }

        return new Step('Generating plugin files', $instructions);
    }

    /**
     * Creates an instance from an array.
     *
     * @param array $data An array of data containing the project's configuration.
     */
    public static function fromArray(string $path, array $data): self
    {
        if (!array_key_exists('info', $data)) {
            throw new RuntimeException('Missing "info" in project configuration.');
        }

        $project = new self($path, new Config(), Info::fromArray($data['info']), []);

        foreach ($data as $key => $value) {
            switch ($key) {
                case 'config':
                    $project->config = new Config($value ?? []);
                    break;
                case 'builds':
                    $project->builds = [];
                    foreach ($value ?? [] as $name => $buildData) {
                        $project->builds[] = Build::fromArray($name, $project, $buildData);
                    }
                    break;
                case 'info':
                    break;
                default:
                    throw new RuntimeException("Invalid project key: $key");
            }
        }

        return $project;
    }

    /** Creates an instance from a JSON file. */
    public static function fromJsonFile(string $jsonFile): self
    {
        $f = fopen($jsonFile, 'r');
        if ($f) {
            fclose($f);
        }

        if (!file_exists($jsonFile)) {
            throw new RuntimeException("File not found: {$jsonFile}");
        }

        if (!is_readable($jsonFile)) {
            throw new RuntimeException("Could not read JSON file: {$jsonFile}");
        }

        $json = file_get_contents($jsonFile);
        $data = @json_decode($json, true);

        if ($data === null) {
            throw new RuntimeException("Could not decode JSON file: {$jsonFile}");
        }

        return self::fromArray(dirname($jsonFile), $data);
    }
}
