<?php

namespace RebelCode\Mantle\Project;

use InvalidArgumentException;

class Step
{
    /** @var string */
    protected $name;
    /** @var Instruction[] */
    protected $instructions;

    /**
     * Constructor.
     *
     * @param string $name The name of the step.
     * @param Instruction[] $instructions The instructions to execute.
     */
    public function __construct(string $name, array $instructions = [])
    {
        $this->name = $name;
        $this->instructions = $instructions;
    }

    /** Retrieves the step's name. */
    public function getName(): string
    {
        return $this->name;
    }

    /** Sets the step's name. */
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Retrieves the step's instructions.
     *
     * @return Instruction[]
     */
    public function getInstructions(): array
    {
        return $this->instructions;
    }

    /**
     * Creates an instance from an array.
     *
     * @param string $name The name of the step.
     * @param Config $config The project's configuration.
     * @param array $data An array of data containing the step's instructions.
     */
    public static function fromArray(string $name, Config $config, array $data = []): self
    {
        $instructions = [];

        foreach ($data as $instruction) {
            $instruction = array_values($instruction);

            if (count($instruction) === 0) {
                throw new InvalidArgumentException('Empty instruction');
            }

            $command = strtolower($instruction[0]);
            $args = array_slice($instruction, 1);

            $instructions[] = $config->createInstruction($command, $args);
        }

        return new self($name, $instructions);
    }

    /**
     * Runs the step.
     *
     * @param Build $build The build which the step is running for.
     */
    public function run(Build $build)
    {
        $build->getProject()->getIo()->writeStep($this->name);

        foreach ($this->instructions as $instruction) {
            $instruction->run($build);
        }

        $build->getProject()->getIo()->endStep();
    }
}
