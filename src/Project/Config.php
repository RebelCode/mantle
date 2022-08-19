<?php

namespace RebelCode\Mantle\Project;

use InvalidArgumentException;
use RebelCode\Mantle\InstructionType;
use RebelCode\Mantle\InstructionType\AddInstructionType;
use RebelCode\Mantle\InstructionType\GenerateInstructionType;
use RebelCode\Mantle\InstructionType\RemoveInstructionType;
use RebelCode\Mantle\InstructionType\RunInstructionType;
use RebelCode\Mantle\Svn\SvnConfig;

class Config
{
    /** @var string */
    public $tempDir;
    /** @var bool */
    public $keepTempDir;
    /** @var string */
    public $zipFileTemplate;
    /** @var string|null */
    public $devBuildName;
    /** @var SvnConfig|null */
    public $svn;
    /** @var array<string,InstructionType> */
    public $instructionTypes;

    /** Constructor. */
    public function __construct(array $data = [])
    {
        $this->tempDir = rtrim($data['tempDir'] ?? sys_get_temp_dir(), '\\/');
        $this->keepTempDir = $data['keepTempDir'] ?? false;
        $this->zipFileTemplate = $data['zipFile'] ?? '{{slug}}-{{version}}-{{build}}.zip';
        $this->devBuildName = $data['devBuild'] ?? null;
        $this->svn = isset($data['svn']) ? SvnConfig::fromArray($data['svn']) : null;
        $this->instructionTypes = [
            'add' => new AddInstructionType(),
            'generate' => new GenerateInstructionType(),
            'remove' => new RemoveInstructionType(),
            'run' => new RunInstructionType(),
        ];
    }

    /** Retrieves the path to the temporary build files directory. */
    public function getTempDir(): string
    {
        return $this->tempDir;
    }

    /** Retrieves the name of the build to use by default for development. */
    public function getDevBuildName(): ?string
    {
        return $this->devBuildName;
    }

    /** Retrieves the template for zip file filenames. */
    public function getZipFileTemplate(): string
    {
        return $this->zipFileTemplate;
    }

    /**
     * Retrieves the instruction type instances.
     *
     * @return InstructionType[]
     */
    public function getInstructionTypes(): array
    {
        return $this->instructionTypes;
    }

    /** Adds an instruction type. */
    public function addInstructionType(string $name, InstructionType $type): self
    {
        $this->instructionTypes[$name] = $type;
        return $this;
    }

    /** Creates an instruction instance from a type and list of arguments. */
    public function createInstruction(string $typeName, array $args): Instruction
    {
        $type = $this->instructionTypes[$typeName] ?? null;

        if ($type === null) {
            throw new InvalidArgumentException('Unknown instruction type: ' . $typeName);
        } else {
            return new Instruction($type, $args);
        }
    }
}
