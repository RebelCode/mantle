<?php

namespace RebelCode\Mantle\Tests\InstructionType;

use PHPUnit\Framework\TestCase;
use RebelCode\Mantle\InstructionType;
use RebelCode\Mantle\InstructionType\GenerateInstructionType;
use RebelCode\Mantle\Tests\InstructionTestTrait;

class GenerateInstructionTypeTest extends TestCase
{
    use InstructionTestTrait;

    public function test_it_should_implement_instruction_interface()
    {
        $this->assertInstanceOf(
            InstructionType::class,
            new GenerateInstructionType(),
            GenerateInstructionType::class . ' must implement ' . InstructionType::class . ' interface'
        );
    }

    public function test_it_should_generate_file()
    {
        [$project, $vfs] = $this->createMockProject(
            [
                'template.txt' => <<<TEMPLATE
                    This is a template file for {{name}}.
                    The version is {{version}}.
                    Some more info about the project:
                    * {{description}}
                    * {{author}}
                    * {{license}}
                    TEMPLATE,
            ],
            []
        );

        $expected = <<<EXPECTED
            This is a template file for {$project->getInfo()->name}.
            The version is {$project->getInfo()->version}.
            Some more info about the project:
            * {$project->getInfo()->description}
            * {$project->getInfo()->author}
            * {$project->getInfo()->license}
            EXPECTED;

        $instruction = new GenerateInstructionType();
        $instruction->run($project->getBuild('test'), ['template.txt', 'generated.txt']);

        $this->assertFileExists($vfs->url() . '/tmp/generated.txt');
        $this->assertEquals($expected, file_get_contents($vfs->url() . '/tmp/generated.txt'));
    }

    public function test_it_should_throw_if_template_file_does_not_exist()
    {
        $this->expectException(\RuntimeException::class);

        [$project] = $this->createMockProject(
            [
                'template.txt' => '',
            ],
            []
        );

        $instruction = new GenerateInstructionType();
        $instruction->run($project->getBuild('test'), ['wrong-file.txt', 'generated.txt']);
    }

    public function test_it_should_throw_if_template_file_is_a_dir()
    {
        $this->expectException(\RuntimeException::class);

        [$project] = $this->createMockProject(
            [
                'template' => [],
            ],
            []
        );

        $instruction = new GenerateInstructionType();
        $instruction->run($project->getBuild('test'), ['template', 'generated.txt']);
    }
}
