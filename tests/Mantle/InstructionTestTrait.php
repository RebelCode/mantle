<?php

namespace RebelCode\Mantle\Tests;

use bovigo\vfs\vfsStream;
use bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\MockObject\MockObject;
use RebelCode\Mantle\Project;
use RebelCode\Mantle\Project\Build;

trait InstructionTestTrait
{
    /**
     * @param array $projectDir
     * @param array $tempDir
     * @return array{0: Project, 1: vfsStreamDirectory}
     */
    public function createMockProject(array $projectDir = [], array $tempDir = []): array
    {
        $vfs = vfsStream::setup('root', null, [
            'project' => $projectDir,
            'tmp' => $tempDir,
        ]);

        $config = $this->createMock(Project\Config::class);
        $config->tempDir = $vfs->url() . '/tmp';

        $project = new Project(
            $vfs->url() . '/project',
            $config,
            Project\Info::fromArray([
                'name' => 'Test Plugin',
                'version' => '1.2.3',
                'mainFile' => 'my-plugin.php',
                'slug' => 'test-plugin',
                'shortId' => 'test',
                'constantId' => 'TEST',
                'description' => 'This is a test plugin used in tests',
                'url' => 'https://example.com',
                'author' => 'John Doe',
                'authorUrl' => 'https://example.com/author',
                'textDomain' => 'test-plugin',
                'domainPath' => '/my-domain',
                'minWpVer' => '4.0',
                'minPhpVer' => '7.0',
            ])
        );

        $project->addBuild(new Build('test', $project));

        return [$project, $vfs];
    }

    /**
     * @psalm-template RealInstanceType of object
     * @psalm-param class-string<RealInstanceType>|string[] $class
     * @psalm-return MockObject&RealInstanceType
     */
    abstract public function createMock(string $class): MockObject;
}
