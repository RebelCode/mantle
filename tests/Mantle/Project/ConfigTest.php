<?php

namespace RebelCode\Mantle\Tests\Project;

use PHPUnit\Framework\TestCase;
use RebelCode\Mantle\InstructionType\AddInstructionType;
use RebelCode\Mantle\InstructionType\GenerateInstructionType;
use RebelCode\Mantle\InstructionType\RemoveInstructionType;
use RebelCode\Mantle\InstructionType\RunInstructionType;
use RebelCode\Mantle\Project\Config;
use RebelCode\Mantle\Svn\SvnConfig;

class ConfigTest extends TestCase
{
    public function test_it_should_construct()
    {
        $array = [
            'tempDir' => './build',
            'keepTempDir' => true,
            'zipFile' => 'my-plugin.zip',
            'devBuild' => 'my-build',
            'svn' => [
                'build' => 'my_build',
                'trunkCommit' => 'Test commit message',
                'tagCommit' => 'Test commit message',
                'checkoutDir' => './.svn',
            ],
        ];

        $config = new Config($array);

        $this->assertSame('./build', $config->tempDir);
        $this->assertTrue($config->keepTempDir);
        $this->assertSame('my-plugin.zip', $config->zipFileTemplate);
        $this->assertSame('my-build', $config->devBuildName);

        $this->assertInstanceOf(SvnConfig::class, $config->svn);
        $this->assertSame($array['svn']['build'], $config->svn->build);
        $this->assertEquals($array['svn']['trunkCommit'], $config->svn->trunkCommit);
        $this->assertEquals($array['svn']['tagCommit'], $config->svn->tagCommit);
        $this->assertEquals($array['svn']['checkoutDir'], $config->svn->checkoutDir);
    }

    public function test_it_should_strip_trailing_slash_in_temp_dir()
    {
        $array = ['tempDir' => './temp/custom-dir/',];

        $config = new Config($array);

        $this->assertSame('./temp/custom-dir', $config->tempDir);
    }

    public function test_it_should_create_with_defaults()
    {
        $config = new Config();

        $this->assertEquals(sys_get_temp_dir(), $config->tempDir);
        $this->assertFalse($config->keepTempDir);
        $this->assertSame('{{slug}}-{{version}}-{{build}}.zip', $config->zipFileTemplate);
        $this->assertArrayHasKey('add', $config->instructionTypes);
        $this->assertArrayHasKey('remove', $config->instructionTypes);
        $this->assertArrayHasKey('run', $config->instructionTypes);
        $this->assertArrayHasKey('generate', $config->instructionTypes);
        $this->assertNull($config->svn);
    }

    public function test_it_should_create_an_add_instruction()
    {
        $config = new Config();
        $instruction = $config->createInstruction('add', ['foo', 'bar']);

        $this->assertInstanceOf(AddInstructionType::class, $instruction->getType());
        $this->assertEquals(['foo', 'bar'], $instruction->getArgs());
    }

    public function test_it_should_create_a_remove_instruction()
    {
        $config = new Config();
        $instruction = $config->createInstruction('remove', ['foo', 'bar']);

        $this->assertInstanceOf(RemoveInstructionType::class, $instruction->getType());
        $this->assertEquals(['foo', 'bar'], $instruction->getArgs());
    }

    public function test_it_should_create_a_generate_instruction()
    {
        $config = new Config();
        $instruction = $config->createInstruction('generate', ['input', 'output']);

        $this->assertInstanceOf(GenerateInstructionType::class, $instruction->getType());
        $this->assertEquals(['input', 'output'], $instruction->getArgs());
    }

    public function test_it_should_create_a_run_instruction()
    {
        $config = new Config();
        $instruction = $config->createInstruction('run', ['do stuff']);

        $this->assertInstanceOf(RunInstructionType::class, $instruction->getType());
        $this->assertEquals(['do stuff'], $instruction->getArgs());
    }
}
