<?php

namespace RebelCode\Mantle\Project;

use RebelCode\Mantle\InstructionType;

/** A tuple containing the instruction type and the arguments to pass to it. */
class Instruction
{
    /** @var InstructionType */
    protected $type;
    /** @var array */
    protected $args;

    /**
     * @param InstructionType $type
     * @param array $args
     */
    public function __construct(InstructionType $type, array $args = [])
    {
        $this->type = $type;
        $this->args = $args;
    }

    /** Retrieves the instruction's type. */
    public function getType(): InstructionType
    {
        return $this->type;
    }

    /** Retrieves the instruction's arguments. */
    public function getArgs(): array
    {
        return $this->args;
    }

    /** Runs the instruction. */
    public function run(Build $build)
    {
        $this->type->run($build, $this->args, $build->getProject()->getIo());
    }
}
