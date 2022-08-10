<?php

namespace RebelCode\Mantle;

interface InstructionType
{
    /** Runs the instruction for a given build with a specific set of arguments. */
    public function run(Project\Build $build, array $args): void;
}
