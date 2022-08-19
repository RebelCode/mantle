<?php

namespace RebelCode\Mantle\Tests\Project;

use PHPUnit\Framework\TestCase;
use RebelCode\Mantle\InstructionType\AddFiles;
use RebelCode\Mantle\InstructionType\GenerateFiles;
use RebelCode\Mantle\InstructionType\RemoveFiles;
use RebelCode\Mantle\InstructionType\RunCommand;
use RebelCode\Mantle\Project\Config;

class ConfigTest extends TestCase
{
    public function test_it_should_create_with_defaults()
    {
        $config = new Config();

        $this->assertEquals(sys_get_temp_dir(), $config->buildDir);
        $this->assertSame('{{slug}}-{{version}}-{{build}}.zip', $config->zipFile);
        $this->assertNull($config->devBuild);
        $this->assertNull($config->publishBuild);
        $this->assertEquals('.wporg', $config->checkoutDir);
        $this->assertEquals('Update trunk to v{{version}}', $config->trunkCommit);
        $this->assertEquals('Add tag {{version}}', $config->tagCommit);
        $this->assertArrayHasKey('add', $config->instructionTypes);
        $this->assertArrayHasKey('remove', $config->instructionTypes);
        $this->assertArrayHasKey('run', $config->instructionTypes);
        $this->assertArrayHasKey('generate', $config->instructionTypes);
    }

    public function test_it_should_create_from_array()
    {
        $array = [
            'buildDir' => './build',
            'zipFile' => 'my-plugin.zip',
            'devBuild' => 'my-build',
            'publishBuild' => 'my_build',
            'trunkCommit' => 'Test commit message',
            'tagCommit' => 'Test commit message',
            'checkoutDir' => './.svn',
        ];

        $config = Config::fromArray($array);

        $this->assertSame($array['buildDir'], $config->buildDir);
        $this->assertSame($array['zipFile'], $config->zipFile);
        $this->assertSame($array['devBuild'], $config->devBuild);
        $this->assertSame($array['publishBuild'], $config->publishBuild);
        $this->assertEquals($array['trunkCommit'], $config->trunkCommit);
        $this->assertEquals($array['tagCommit'], $config->tagCommit);
        $this->assertEquals($array['checkoutDir'], $config->checkoutDir);
    }

    public function test_it_should_strip_trailing_slash_in_build_dir()
    {
        $array = ['buildDir' => './temp/custom-dir/',];
        $config = Config::fromArray($array);

        $this->assertSame('./temp/custom-dir', $config->buildDir);
    }

    public function test_it_should_create_an_add_instruction()
    {
        $config = new Config();
        $instruction = $config->createInstruction('add', ['foo', 'bar']);

        $this->assertInstanceOf(AddFiles::class, $instruction->getType());
        $this->assertEquals(['foo', 'bar'], $instruction->getArgs());
    }

    public function test_it_should_create_a_remove_instruction()
    {
        $config = new Config();
        $instruction = $config->createInstruction('remove', ['foo', 'bar']);

        $this->assertInstanceOf(RemoveFiles::class, $instruction->getType());
        $this->assertEquals(['foo', 'bar'], $instruction->getArgs());
    }

    public function test_it_should_create_a_generate_instruction()
    {
        $config = new Config();
        $instruction = $config->createInstruction('generate', ['input', 'output']);

        $this->assertInstanceOf(GenerateFiles::class, $instruction->getType());
        $this->assertEquals(['input', 'output'], $instruction->getArgs());
    }

    public function test_it_should_create_a_run_instruction()
    {
        $config = new Config();
        $instruction = $config->createInstruction('run', ['do stuff']);

        $this->assertInstanceOf(RunCommand::class, $instruction->getType());
        $this->assertEquals(['do stuff'], $instruction->getArgs());
    }
}
