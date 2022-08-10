<?php

namespace RebelCode\Mantle\Tests\Project;

use PHPUnit\Framework\TestCase;
use RebelCode\Mantle\InstructionType\AddInstructionType;
use RebelCode\Mantle\InstructionType\GenerateInstructionType;
use RebelCode\Mantle\InstructionType\RemoveInstructionType;
use RebelCode\Mantle\InstructionType\RunInstructionType;
use RebelCode\Mantle\Project;
use RebelCode\Mantle\Project\Build;
use RebelCode\Mantle\Project\Info;
use RebelCode\Mantle\Project\Instruction;
use RebelCode\Mantle\Project\Step;

class BuildTest extends TestCase
{
    public function test_it_should_construct()
    {
        $name = 'test-build';
        $project = $this->createMock(Project::class);
        $inherits = ['build-1', 'build-2'];
        $info = $this->createMock(Info::class);
        $env = ['FOO' => 'BAR'];
        $steps = [
            'step-1' => $this->createMock(RunInstructionType::class),
            'step-2' => $this->createMock(RunInstructionType::class),
        ];

        $build = new Build($name, $project, $inherits, $info, $env, $steps);

        $this->assertEquals($name, $build->getName());
        $this->assertSame($project, $build->getProject());
        $this->assertSame($inherits, $build->getParents());
        $this->assertSame($info, $build->getInfo());
        $this->assertSame($env, $build->getEnv());
        $this->assertSame($steps, $build->getSteps());
    }

    public function test_it_should_add_parent()
    {
        $inherits = [
            'build-1',
            'build-2',
        ];

        $build = new Build('', $this->createMock(Project::class), $inherits);
        $build->addParent($newParent = 'build-3');

        $this->assertCount(3, $build->getParents(), 'It should have 3 parent builds');
        $this->assertEquals($inherits[0], $build->getParents()[0], 'It should still have the previous parent builds');
        $this->assertEquals($inherits[1], $build->getParents()[1], 'It should still have the previous parent builds');
        $this->assertEquals($newParent, $build->getParents()[2], 'It should have the new parent build');
    }

    public function test_it_should_not_add_parent_twice()
    {
        $inherits = [
            'build-1',
            'build-2',
        ];

        $build = new Build('', $this->createMock(Project::class), $inherits);
        $build->addParent('build-2');

        $this->assertEquals($inherits, $build->getParents(), 'It should still have the previous parent builds');
    }

    public function test_it_should_remove_parent()
    {
        $inherits = [
            'build-1',
            'build-2',
        ];

        $build = new Build('', $this->createMock(Project::class), $inherits);
        $build->removeParent('build-2');

        $this->assertCount(1, $build->getParents(), 'It should only have 1 parent build');
        $this->assertEquals($inherits[0], $build->getParents()[0], 'It should have only the first parent build');
    }

    public function test_it_should_not_remove_nonexistent_parent()
    {
        $inherits = [
            'build-1',
            'build-2',
        ];

        $build = new Build('', $this->createMock(Project::class), $inherits);
        $build->removeParent('build-3');

        $this->assertEquals($inherits, $build->getParents(), 'It should still have the same parent builds');
    }

    public function test_it_should_set_parents()
    {
        $oldInherits = [
            'build-1',
            'build-2',
        ];
        $newInherits = [
            'build-3',
            'build-4',
        ];

        $build = new Build('', $this->createMock(Project::class), $oldInherits);
        $result = $build->setParents($newInherits);

        $this->assertEquals($newInherits, $build->getParents(), 'It should have the new parent builds');
        $this->assertSame($build, $result, 'It should return itself for a fluent interface');
    }

    public function test_it_should_set_env()
    {
        $oldEnv = [
            'FOO' => 'BAR',
            'BAR' => 'BAZ',
        ];

        $newEnv = [
            'BAZ' => 'QUX',
            'QUX' => 'QUUX',
        ];

        $build = new Build('', $this->createMock(Project::class), [], $this->createMock(Info::class), $oldEnv);
        $result = $build->setEnv($newEnv);

        $this->assertEquals($newEnv, $build->getEnv(), 'It should have the new env');
        $this->assertSame($build, $result, 'It should return itself for a fluent interface');
    }

    public function test_it_should_add_env()
    {
        $env = [
            'FOO' => 'BAR',
            'BAZ' => 'QUX',
        ];

        $build = new Build('', $this->createMock(Project::class), [], null, $env);
        $result = $build->addEnv(
            $newEnv = [
                'BAZ' => 'NEW_BAZ',
                'QUX' => 'QUUX',
            ]
        );

        $this->assertCount(3, $build->getEnv(), 'It should have 3 environment variables');
        $this->assertEquals($env['FOO'], $build->getEnv()['FOO'], 'It should still have the previous FOO value');
        $this->assertEquals($newEnv['BAZ'], $build->getEnv()['BAZ'], 'It should have the new BAZ value');
        $this->assertEquals($newEnv['QUX'], $build->getEnv()['QUX'], 'It should have the new QUX value');
        $this->assertSame($build, $result, 'It should return itself for a fluent interface');
    }

    public function test_it_should_remove_env()
    {
        $env = [
            'FOO' => 'BAR',
            'BAZ' => 'QUX',
        ];

        $build = new Build('', $this->createMock(Project::class), [], null, $env);
        $result = $build->removeEnv('BAZ');

        $this->assertCount(1, $build->getEnv(), 'It should only have 1 environment variable');
        $this->assertEquals($env['FOO'], $build->getEnv()['FOO'], 'It should have the previous FOO value');
        $this->assertSame($build, $result, 'It should return itself for a fluent interface');
    }

    public function test_it_should_not_remove_nonexistent_env()
    {
        $env = [
            'FOO' => 'BAR',
            'BAZ' => 'QUX',
        ];

        $build = new Build('', $this->createMock(Project::class), [], null, $env);
        $build->removeEnv('QUX');

        $this->assertEquals($env, $build->getEnv(), 'It should have the previous environment variables');
    }

    public function test_it_should_get_step_by_name()
    {
        $build = new Build('', $this->createMock(Project::class), [], null, [], [
            $first = $this->createTestProxy(Step::class, ['first']),
            $second = $this->createTestProxy(Step::class, ['second']),
        ]);

        $this->assertSame($first, $build->getStep('first'), 'It should return the first step');
        $this->assertSame($second, $build->getStep('second'), 'It should return the second step');
    }

    public function test_it_should_return_null_if_step_not_found()
    {
        $build = new Build('', $this->createMock(Project::class), [], null, [], [
            $this->createTestProxy(Step::class, ['first']),
            $this->createTestProxy(Step::class, ['second']),
        ]);

        $this->assertNull($build->getStep('third'), 'It should return null if the step is not found');
    }

    public function test_it_should_add_new_step()
    {
        $build = new Build('', $this->createMock(Project::class), [], null, [], [
            $this->createTestProxy(Step::class, ['first']),
            $this->createTestProxy(Step::class, ['second']),
        ]);

        $newStep = $this->createTestProxy(Step::class, ['third']);
        $result = $build->addStep($newStep);

        $this->assertSame($build, $result, 'It should return itself for a fluent interface');
        $this->assertCount(3, $build->getSteps(), 'It should have 3 steps');
        $this->assertSame($newStep, $build->getStep('third'), 'It should have the new step');
    }

    public function test_it_should_add_new_step_at_index()
    {
        $build = new Build('', $this->createMock(Project::class), [], null, [], [
            $one = $this->createTestProxy(Step::class, ['one']),
            $two = $this->createTestProxy(Step::class, ['two']),
        ]);

        $new = $this->createTestProxy(Step::class, ['one_point_five']);
        $result = $build->addStep($new, 1);

        $expected = [$one, $new, $two];

        $this->assertEquals($expected, $build->getSteps(), 'It should have the new step at the specified index');
        $this->assertSame($build, $result, 'It should return itself for a fluent interface');
    }

    public function test_it_should_override_step_with_same_name()
    {
        $build = new Build('', $this->createMock(Project::class), [], null, [], [
            $one = $this->createTestProxy(Step::class, ['one']),
            $two = $this->createTestProxy(Step::class, ['two']),
        ]);

        $new = $this->createTestProxy(Step::class, ['one']);
        $result = $build->addStep($new);

        $expected = [$new, $two];

        $this->assertEquals($expected, $build->getSteps(), 'It should have the new step at the specified index');
        $this->assertSame($build, $result, 'It should return itself for a fluent interface');
    }

    public function test_it_should_override_step_with_same_name_and_ignore_index()
    {
        $build = new Build('', $this->createMock(Project::class), [], null, [], [
            $one = $this->createTestProxy(Step::class, ['one']),
            $two = $this->createTestProxy(Step::class, ['two']),
        ]);

        $new = $this->createTestProxy(Step::class, ['two']);
        $result = $build->addStep($new, 0);

        $expected = [$one, $new];

        $this->assertEquals($expected, $build->getSteps(), 'It should have the new step at the specified index');
        $this->assertSame($build, $result, 'It should return itself for a fluent interface');
    }

    public function test_it_should_set_new_steps()
    {
        $build = new Build('', $this->createMock(Project::class), [], null, [], [
            $this->createTestProxy(Step::class, ['first']),
            $this->createTestProxy(Step::class, ['second']),
        ]);

        $result = $build->setSteps(
            $newSteps = [
                $this->createTestProxy(Step::class, ['third']),
                $this->createTestProxy(Step::class, ['fourth']),
            ]
        );

        $this->assertSame($newSteps, $build->getSteps(), 'It should have the new steps');
        $this->assertSame($build, $result, 'It should return itself for a fluent interface');
    }

    public function test_it_should_remove_step_by_name()
    {
        $build = new Build('', $this->createMock(Project::class), [], null, [], [
            $first = $this->createTestProxy(Step::class, ['first']),
            $second = $this->createTestProxy(Step::class, ['second']),
        ]);

        $result = $build->removeStep('first');

        $this->assertCount(1, $build->getSteps(), 'It should have 1 step');
        $this->assertSame($second, $build->getStep('second'), 'It should still have the second step');
        $this->assertSame($build, $result, 'It should return itself for a fluent interface');
    }

    public function test_it_should_not_remove_nonexistent_step()
    {
        $build = new Build('', $this->createMock(Project::class), [], null, [], $steps = [
            $this->createTestProxy(Step::class, ['first']),
            $this->createTestProxy(Step::class, ['second']),
        ]);

        $build->removeStep('third');

        $this->assertEquals($steps, $build->getSteps(), 'It should still have the same steps');
    }

    public function test_it_should_construct_from_array()
    {
        $array = [
            'info' => [
                'version' => '1.2.3',
            ],
            'inherits' => ['build-1', 'build-2'],
            'env' => [
                'FOO' => 'BAR',
            ],
            'steps' => [
                'first' => [
                    ['add', 'foo', 'bar'],
                    ['generate', 'foo', 'bar'],
                    ['remove', 'foo', 'bar'],
                ],
                'second' => [
                    ['run', 'foo', 'bar'],
                ],
            ],
        ];

        $project = Project::fromArray(__DIR__, [
            'info' => [
                'name' => 'Foo',
                'version' => '4.5.6',
                'mainFile' => 'foo.php',
            ],
        ]);

        $build = Build::fromArray($name = 'test-build', $project, $array);

        $this->assertEquals($name, $build->getName());
        $this->assertSame($project, $build->getProject());
        $this->assertSame($array['inherits'], $build->getParents());
        $this->assertSame($array['env'], $build->getEnv());

        $this->assertEquals($project->getInfo()->name, $build->getInfo()->name);
        $this->assertEquals($project->getInfo()->mainFile, $build->getInfo()->mainFile);
        $this->assertEquals($array['info']['version'], $build->getInfo()->version);

        $step1 = $build->getStep('first');
        $step2 = $build->getStep('second');

        $this->assertInstanceOf(Project\Step::class, $step1);
        $this->assertInstanceOf(Project\Step::class, $step1);
        $this->assertInstanceOf(Project\Step::class, $step1);
        $this->assertInstanceOf(Project\Step::class, $step2);

        $this->assertInstanceOf(Instruction::class, $step1->getInstructions()[0]);
        $this->assertInstanceOf(Instruction::class, $step1->getInstructions()[1]);
        $this->assertInstanceOf(Instruction::class, $step1->getInstructions()[2]);
        $this->assertInstanceOf(Instruction::class, $step2->getInstructions()[0]);

        $this->assertInstanceOf(AddInstructionType::class, $step1->getInstructions()[0]->getType());
        $this->assertInstanceOf(GenerateInstructionType::class, $step1->getInstructions()[1]->getType());
        $this->assertInstanceOf(RemoveInstructionType::class, $step1->getInstructions()[2]->getType());
        $this->assertInstanceOf(RunInstructionType::class, $step2->getInstructions()[0]->getType());
    }
}
