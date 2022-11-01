<?php

namespace RebelCode\Mantle\Tests\InstructionType;

use PHPUnit\Framework\TestCase;
use RebelCode\Mantle\InstructionType;
use RebelCode\Mantle\InstructionType\RemoveFiles;
use RebelCode\Mantle\MantleOutputStyle;
use RebelCode\Mantle\Tests\InstructionTestTrait;

class RemoveFilesTest extends TestCase
{
    use InstructionTestTrait;

    public function test_it_should_implement_instruction_interface()
    {
        $this->assertInstanceOf(
            InstructionType::class,
            new RemoveFiles(),
            RemoveFiles::class . ' must implement ' . InstructionType::class . ' interface'
        );
    }

    public function test_it_should_delete_file()
    {
        [$project, $vfs] = $this->createMockProject(
            [],
            [
                'foo.txt' => 'Lorem ipsum',
            ]
        );

        $io = $this->createMock(MantleOutputStyle::class);

        $instruction = new RemoveFiles();
        $instruction->run($project->getBuild('test'), ['foo.txt'], $io);

        $this->assertFileNotExists($vfs->url() . '/tmp/foo.txt');
    }

    public function test_it_should_delete_multiple_files()
    {
        [$project, $vfs] = $this->createMockProject(
            [],
            [
                'foo.txt' => 'Lorem ipsum',
                'bar.txt' => 'dolor sit amet',
                'baz.txt' => 'consectetur adipiscing elit',
            ]
        );

        $io = $this->createMock(MantleOutputStyle::class);

        $instruction = new RemoveFiles();
        $instruction->run($project->getBuild('test'), ['foo.txt', 'bar.txt', 'baz.txt'], $io);

        $this->assertFileNotExists($vfs->url() . '/tmp/foo.txt');
        $this->assertFileNotExists($vfs->url() . '/tmp/bar.txt');
        $this->assertFileNotExists($vfs->url() . '/tmp/baz.txt');
    }

    public function test_it_should_delete_directories_recursively()
    {
        [$project, $vfs] = $this->createMockProject(
            [],
            [
                'dir' => [
                    'foo.txt' => 'Lorem ipsum',
                    'sub' => [
                        'bar.txt' => 'dolor sit amet',
                    ],
                ],
            ]
        );

        $io = $this->createMock(MantleOutputStyle::class);

        $instruction = new RemoveFiles();
        $instruction->run($project->getBuild('test'), ['dir'], $io);

        $this->assertDirectoryNotExists($vfs->url() . '/tmp/dir');
        $this->assertDirectoryNotExists($vfs->url() . '/tmp/dir/sub');
        $this->assertFileNotExists($vfs->url() . '/tmp/dir/foo.txt');
        $this->assertFileNotExists($vfs->url() . '/tmp/dir/sub/bar.txt');
    }
}
