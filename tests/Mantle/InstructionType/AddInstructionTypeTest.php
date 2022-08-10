<?php

namespace RebelCode\Mantle\Tests\InstructionType;

use PHPUnit\Framework\TestCase;
use RebelCode\Mantle\InstructionType;
use RebelCode\Mantle\InstructionType\AddInstructionType;
use RebelCode\Mantle\Tests\InstructionTestTrait;

class AddInstructionTypeTest extends TestCase
{
    use InstructionTestTrait;

    public function test_it_should_implement_instruction_interface()
    {
        $this->assertInstanceOf(
            InstructionType::class,
            new AddInstructionType(),
            AddInstructionType::class . ' must implement ' . InstructionType::class . ' interface'
        );
    }

    public function test_it_should_copy_file()
    {
        [$project, $vfs] = $this->createMockProject(
            [
                'foo.txt' => 'Lorem ipsum',
            ],
            []
        );

        $instruction = new AddInstructionType();
        $instruction->run($project->getBuild('test'), ['foo.txt']);

        $this->assertFileExists($vfs->url() . '/tmp/foo.txt');
    }

    public function test_it_should_copy_multiple_files()
    {
        [$project, $vfs] = $this->createMockProject(
            [
                'foo.txt' => 'Lorem ipsum',
                'bar.txt' => 'dolor sit amet',
                'baz.txt' => 'consectetur adipiscing elit',
            ],
            []
        );

        $instruction = new AddInstructionType();
        $instruction->run($project->getBuild('test'), ['foo.txt', 'bar.txt', 'baz.txt']);

        $this->assertFileExists($vfs->url() . '/tmp/foo.txt');
        $this->assertFileExists($vfs->url() . '/tmp/bar.txt');
        $this->assertFileExists($vfs->url() . '/tmp/baz.txt');
    }

    public function test_it_should_copy_directory_recursively()
    {
        [$project, $vfs] = $this->createMockProject(
            [
                'dir' => [
                    'foo.txt' => 'Lorem ipsum',
                    'sub' => [
                        'bar.txt' => 'dolor sit amet',
                    ],
                ],
            ],
            []
        );

        $instruction = new AddInstructionType();
        $instruction->run($project->getBuild('test'), ['dir']);

        $this->assertFileExists($vfs->url() . '/tmp/dir/foo.txt');
        $this->assertFileExists($vfs->url() . '/tmp/dir/sub/bar.txt');
    }
}
