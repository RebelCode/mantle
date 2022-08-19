<?php

namespace RebelCode\Mantle\Project;

use InvalidArgumentException;
use RebelCode\Mantle\InstructionType;
use RebelCode\Mantle\InstructionType\AddInstructionType;
use RebelCode\Mantle\InstructionType\GenerateInstructionType;
use RebelCode\Mantle\InstructionType\RemoveInstructionType;
use RebelCode\Mantle\InstructionType\RunInstructionType;

class Config
{
    /** @var string */
    public $buildDir;
    /** @var string */
    public $zipFile = '{{slug}}-{{version}}-{{build}}.zip';
    /** @var string|null */
    public $devBuild = null;
    /** @var string|null */
    public $publishBuild = null;
    /** @var string */
    public $trunkCommit = 'Update trunk to v{{version}}';
    /** @var string */
    public $tagCommit = 'Add tag {{version}}';
    /** @var string|null */
    public $checkoutDir = '.wporg';
    /** @var array<string,InstructionType> */
    public $instructionTypes;

    /** Constructor. */
    public function __construct()
    {
        $this->buildDir = sys_get_temp_dir();
        $this->devBuild = $data['devBuild'] ?? null;
        $this->instructionTypes = [
            'add' => new AddInstructionType(),
            'generate' => new GenerateInstructionType(),
            'remove' => new RemoveInstructionType(),
            'run' => new RunInstructionType(),
        ];
    }

    /** Creates a config instance from an array of data. */
    public static function fromArray(array $data): self
    {
        $config = new self();

        foreach ($data as $key => $value) {
            if (!property_exists(self::class, $key)) {
                throw new InvalidArgumentException("Invalid property \"$key\" in config");
            }

            switch ($key) {
                case 'buildDir':
                    $config->buildDir = rtrim($value, '\\/');
                    break;
                default:
                    $config->$key = $value;
                    break;
            }
        }

        return $config;
    }

    /** Retrieves the file name of the zip file for a build. */
    public function getZipFileName(Build $build): string
    {
        return $build->interpolate($this->zipFile);
    }

    /** Retrieves the trunk commit message, with any placeholders replaced. */
    public function getSvnTrunkCommitMsg(Build $build): string
    {
        return $build->interpolate($this->trunkCommit);
    }

    /** Retrieves the tag commit message, with any placeholders replaced.  */
    public function getSvnTagCommitMsg(Build $build): string
    {
        return $build->interpolate($this->tagCommit);
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
