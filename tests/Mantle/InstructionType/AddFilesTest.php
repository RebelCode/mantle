<?php

namespace RebelCode\Mantle\Tests\InstructionType;

use PHPUnit\Framework\TestCase;
use RebelCode\Mantle\InstructionType;
use RebelCode\Mantle\InstructionType\AddFiles;
use RebelCode\Mantle\MantleOutputStyle;
use RebelCode\Mantle\Tests\InstructionTestTrait;

class AddFilesTest extends TestCase
{
    use InstructionTestTrait;

    public function test_it_should_implement_instruction_interface()
    {
        $this->assertInstanceOf(
            InstructionType::class,
            new AddFiles(),
            AddFiles::class . ' must implement ' . InstructionType::class . ' interface'
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

        $io = $this->createMock(MantleOutputStyle::class);

        $instruction = new AddFiles();
        $instruction->run($project->getBuild('test'), ['foo.txt'], $io);

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

        $io = $this->createMock(MantleOutputStyle::class);

        $instruction = new AddFiles();
        $instruction->run($project->getBuild('test'), ['foo.txt', 'bar.txt', 'baz.txt'], $io);

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

        $io = $this->createMock(MantleOutputStyle::class);

        $instruction = new AddFiles();
        $instruction->run($project->getBuild('test'), ['dir'], $io);

        $this->assertFileExists($vfs->url() . '/tmp/dir/foo.txt');
        $this->assertFileExists($vfs->url() . '/tmp/dir/sub/bar.txt');
    }
}
