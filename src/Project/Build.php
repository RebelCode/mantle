<?php

namespace RebelCode\Mantle\Project;

use InvalidArgumentException;
use RebelCode\Mantle\Project;

class Build
{
    /** @var string */
    protected $name;
    /** @var Project */
    protected $project;
    /** @var Info */
    protected $info;
    /** @var string[] */
    protected $inherits = [];
    /** @var array<string,string> */
    protected $env = [];
    /** @var Step[] */
    protected $steps = [];
    /** @var array<string,string>|null */
    protected $tokenMap;

    /**
     * Constructor.
     *
     * @param Project $project The project to which the build belongs.
     * @param array $inherits An array of build names that this build inherits from.
     * @param Info|null $info Information about the project. If null, the project's info will be used.
     * @param array $env An associative array of environment variables to set.
     * @param Step[] $steps An array of steps to execute.
     */
    public function __construct(
        string $name,
        Project $project,
        array $inherits = [],
        ?Info $info = null,
        array $env = [],
        array $steps = []
    ) {
        $this->name = $name;
        $this->project = $project;
        $this->info = $info ?? clone $project->getInfo();
        $this->inherits = $inherits;
        $this->env = $env;
        $this->steps = $steps;
    }

    /** Retrieves the build's name. */
    public function getName(): string
    {
        return $this->name;
    }

    /** Retrieves the project that the build belongs to. */
    public function getProject(): Project
    {
        return $this->project;
    }

    /** Retrieves the build's project information. */
    public function getInfo(): Info
    {
        return $this->info;
    }

    /** Sets the build's project information. */
    public function setInfo(Info $info): void
    {
        $this->info = $info;
    }

    /**
     * Retrieves the parent build names.
     *
     * @return string[]
     */
    public function getParents(): array
    {
        return $this->inherits;
    }

    /** Adds a parent build. If the build already has this parent, it won't be added a second time. */
    public function addParent(string $parent): void
    {
        if (!in_array($parent, $this->inherits)) {
            $this->inherits[] = $parent;
        }
    }

    /** Removes a parent build. */
    public function removeParent(string $parent): void
    {
        $this->inherits = array_diff($this->inherits, [$parent]);
    }

    /** Sets the build's parents. */
    public function setParents(array $parents): self
    {
        $this->inherits = $parents;
        return $this;
    }

    /** Retrieves the build's environment variable mappings. */
    public function getEnv(): array
    {
        return $this->env;
    }

    /** Sets the build's environment variable mappings. */
    public function setEnv(array $env): self
    {
        $this->env = $env;
        return $this;
    }

    /** Adds mappings for environment variable. Any variables that already have a mapping will be overridden. */
    public function addEnv(array $env): self
    {
        $this->env = array_merge($this->env, $env);
        return $this;
    }

    /** Removes an environment variable mapping. */
    public function removeEnv(string $name): self
    {
        unset($this->env[$name]);
        return $this;
    }

    /**
     * Retrieves the build's steps.
     *
     * @return array<string,Step>
     */
    public function getSteps(): array
    {
        return $this->steps;
    }

    /** Retrieves a build step by its name. */
    public function getStep(string $name): ?Step
    {
        $idx = $this->searchForStep($name);
        return $idx >= 0 ? $this->steps[$idx] : null;
    }

    /**
     * Sets the build's steps.
     *
     * @param array<string,Step> $steps An associative array of steps to execute.
     */
    public function setSteps(array $steps): self
    {
        $this->steps = $steps;
        return $this;
    }

    /** Adds a build step. If a step with the same name already exists, it will be overridden. */
    public function addStep(Step $step, int $index = -1): self
    {
        $existingIdx = $this->searchForStep($step->getName());

        if ($existingIdx < 0) {
            if ($index < 0) {
                $this->steps[] = $step;
            } else {
                array_splice($this->steps, $index, 0, [$step]);
            }
        } else {
            $this->steps[$existingIdx] = $step;
        }

        return $this;
    }

    /** Removes a build step by its name */
    public function removeStep(string $name): self
    {
        $idx = $this->searchForStep($name);
        if ($idx >= 0) {
            array_splice($this->steps, $idx, 1);
        }

        return $this;
    }

    /** Searches for a step's index by its name. */
    protected function searchForStep(string $name): int
    {
        foreach ($this->steps as $idx => $step) {
            if ($step->getName() === $name) {
                return $idx;
            }
        }

        return -1;
    }

    /** Runs the build. */
    public function run(array $steps = [], ?Build $parent = null)
    {
        if ($parent === null) {
            $this->project->getIo()->writeBuild($this->name);
        }

        // The contextual build instance. This build is passed to the steps, as well as any builds that this
        // build inherits from.
        $ctxBuild = $parent ?? $this;

        // Run parent builds
        foreach ($this->inherits as $parentName) {
            $parent = $this->project->getBuild($parentName);
            if ($parent !== null) {
                $parent->run($steps, $ctxBuild);
            }
        }

        // Set environment variables
        foreach ($this->env as $name => $value) {
            putenv("$name:$value");
        }

        // Run build steps
        foreach ($this->steps as $step) {
            if (count($steps) === 0 || in_array($step->getName(), $steps)) {
                $step->run($ctxBuild);
            }
        }
    }

    /** Retrieves the file name of the zip file for this build. */
    public function getZipFileName(): string
    {
        return $this->interpolate($this->project->getConfig()->zipFileTemplate);
    }

    /** Interpolates a string using the token map. */
    public function interpolate(string $template): string
    {
        return strtr($template, $this->getMetaTokenMap());
    }

    /** Retrieves the token map, building it if necessary. */
    protected function getMetaTokenMap(): array
    {
        if ($this->tokenMap === null) {
            $this->tokenMap = [];

            $data = array_merge($this->info->toArray(), [
                'time' => time(),
                'unique' => uniqid(),
                'build' => $this->name,
            ]);

            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    $this->tokenMap['@{{' . $key . '}}'] = implode(', ', $value);
                    continue;
                }

                if (is_bool($value)) {
                    $this->tokenMap['\'!{{' . $key . '}}\''] = $value ? 'true' : 'false';
                }

                $this->tokenMap['{{' . $key . '}}'] = (string) $value;
            }
        }

        return $this->tokenMap;
    }

    /**
     * Creates an instance from an array of data.
     *
     * @param string $name The name of the build.
     * @param Project $project The project to which the build belongs.
     * @param array $data An array of data containing the build's configuration.
     */
    public static function fromArray(string $name, Project $project, array $data = []): self
    {
        $info = clone $project->getInfo();
        $env = [];
        $inherits = [];
        $steps = [];

        foreach ($data as $key => $value) {
            switch ($key) {
                case 'info':
                    $info->addData($value);
                    break;
                case 'env':
                    $env = $value;
                    break;
                case 'inherits':
                    $inherits = $value;
                    break;
                case 'steps':
                    foreach ($value ?? [] as $stepName => $instructions) {
                        $steps[] = Step::fromArray($stepName, $project->getConfig(), $instructions);
                    }
                    break;
                default:
                    throw new InvalidArgumentException("Invalid build key: $key");
            }
        }

        return new self($name, $project, $inherits, $info, $env, $steps);
    }
}
