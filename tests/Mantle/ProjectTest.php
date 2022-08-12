<?php

namespace RebelCode\Mantle\Tests;

use bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use RebelCode\Mantle\InstructionType\GenerateInstructionType;
use RebelCode\Mantle\MantleOutputStyle;
use RebelCode\Mantle\Project;
use RebelCode\Mantle\Project\Build;
use RebelCode\Mantle\Project\Config;
use RebelCode\Mantle\Project\Info;
use RebelCode\Mantle\Utils;
use ZipArchive;

class ProjectTest extends TestCase
{
    const ZIP_FILE_PATH = MANTLE_TESTS_DIR . '/sample/build.zip';
    const TEMP_DIR_PATH = MANTLE_TESTS_DIR . '/sample/build';

    protected function deleteBuildArtefacts()
    {
        if (file_exists(static::ZIP_FILE_PATH)) {
            unlink(static::ZIP_FILE_PATH);
        }

        if (file_exists(static::TEMP_DIR_PATH)) {
            Utils::rmDirRecursive(static::TEMP_DIR_PATH);
        }
    }

    protected function setUp(): void
    {
        $this->deleteBuildArtefacts();
    }

    protected function tearDown(): void
    {
        $this->deleteBuildArtefacts();
    }

    public function test_it_should_construct()
    {
        $path = './path/to/project';
        $config = $this->createMock(Config::class);
        $info = $this->createMock(Info::class);
        $builds = [
            $this->createMock(Build::class),
            $this->createMock(Build::class),
        ];

        $project = new Project($path, $config, $info, $builds);

        $this->assertSame($path, $project->getPath());
        $this->assertSame($config, $project->getConfig());
        $this->assertSame($info, $project->getInfo());
        $this->assertSame($builds, $project->getBuilds());
    }

    public function test_it_should_set_the_path()
    {
        $path = './path/to/project';
        $config = $this->createMock(Config::class);
        $info = $this->createMock(Info::class);

        $project = new Project($path, $config, $info);
        $project->setPath('/new/path');

        $this->assertSame('/new/path', $project->getPath(), 'It should have the new path.');
    }

    public function test_it_should_set_the_info()
    {
        $path = './path/to/project';
        $config = $this->createMock(Config::class);
        $oldInfo = $this->createMock(Info::class);
        $newInfo = $this->createMock(Info::class);

        $project = new Project($path, $config, $oldInfo);
        $project->setInfo($newInfo);

        $this->assertSame($newInfo, $project->getInfo(), 'It should have the new info.');
    }

    public function test_it_should_set_the_config()
    {
        $path = './path/to/project';
        $oldConfig = $this->createMock(Config::class);
        $newConfig = $this->createMock(Config::class);
        $info = $this->createMock(Info::class);
        $builds = [
            $this->createMock(Build::class),
            $this->createMock(Build::class),
        ];

        $project = new Project($path, $oldConfig, $info, $builds);
        $project->setConfig($newConfig);

        $this->assertSame($newConfig, $project->getConfig(), 'It should have the new config.');
    }

    public function test_it_should_get_build_by_name()
    {
        $path = './path/to/project';
        $config = $this->createMock(Config::class);
        $info = $this->createMock(Info::class);

        $project = new Project($path, $config, $info);
        $project->setBuilds(
            $builds = [
                $this->createTestProxy(Build::class, ['build-1', $project]),
                $this->createTestProxy(Build::class, ['build-2', $project]),
            ]
        );

        $this->assertSame($builds[0], $project->getBuild('build-1'), 'It should return the first build.');
        $this->assertSame($builds[1], $project->getBuild('build-2'), 'It should return the second build.');
    }

    public function test_it_should_add_a_new_build()
    {
        $path = './path/to/project';
        $config = $this->createMock(Config::class);
        $info = $this->createMock(Info::class);

        $project = new Project($path, $config, $info);
        $project->setBuilds(
            $builds = [
                $this->createTestProxy(Build::class, ['build-1', $project]),
                $this->createTestProxy(Build::class, ['build-2', $project]),
            ]
        );

        $newBuild = $this->createTestProxy(Build::class, ['new-build', $project]);
        $project->addBuild($newBuild);

        $this->assertCount(3, $project->getBuilds(), 'It should have 3 builds.');
        $this->assertSame($newBuild, $project->getBuild('new-build'), 'It should add the new build.');
        $this->assertSame($builds[0], $project->getBuilds()[0], 'It should still have the first build.');
        $this->assertSame($builds[1], $project->getBuilds()[1], 'It should still have the second build.');
    }

    public function test_it_should_add_a_build_that_overrides()
    {
        $path = './path/to/project';
        $config = $this->createMock(Config::class);
        $info = $this->createMock(Info::class);

        $project = new Project($path, $config, $info);
        $project->setBuilds(
            $builds = [
                $this->createTestProxy(Build::class, ['build-1', $project]),
                $this->createTestProxy(Build::class, ['build-2', $project]),
            ]
        );

        $newBuild = $this->createTestProxy(Build::class, ['build-1', $project]);
        $project->addBuild($newBuild);

        $this->assertCount(2, $project->getBuilds(), 'It should still have 2 builds.');
        $this->assertSame($newBuild, $project->getBuilds()[0], 'It should override the first build.');
        $this->assertSame($builds[1], $project->getBuilds()[1], 'It should still have the second build.');
    }

    public function test_it_should_set_the_builds()
    {
        $path = './path/to/project';
        $config = $this->createMock(Config::class);
        $info = $this->createMock(Info::class);
        $builds = [
            $this->createMock(Build::class),
            $this->createMock(Build::class),
        ];

        $project = new Project($path, $config, $info, $builds);

        $newBuilds = [
            new Build('build-1', $project),
            new Build('build-2', $project),
            new Build('build-3', $project),
        ];

        $project->setBuilds($newBuilds);

        $this->assertCount(3, $project->getBuilds(), 'It should have 3 builds.');
        $this->assertSame($newBuilds[0], $project->getBuild('build-1'), 'It should have the new first build.');
        $this->assertSame($newBuilds[1], $project->getBuild('build-2'), 'It should have the new second build.');
        $this->assertSame($newBuilds[2], $project->getBuild('build-3'), 'It should have the new third build.');
    }

    public function test_it_should_create_a_build()
    {
        $path = './path/to/project';
        $config = $this->createMock(Config::class);
        $info = $this->createMock(Info::class);
        $builds = [
            'build-1' => $this->createMock(Build::class),
            'build-2' => $this->createMock(Build::class),
        ];

        $project = new Project($path, $config, $info, $builds);
        $result = $project->createBuild('build-3');

        $this->assertCount(3, $project->getBuilds(), 'It should have 3 builds.');
        $this->assertInstanceOf(Build::class, $result, 'It should return a build.');
        $this->assertSame($result, $project->getBuild('build-3'), 'It should have the new build.');
    }

    public function test_it_should_get_the_default_io()
    {
        $project = new Project('./path', $this->createMock(Config::class), $this->createMock(Info::class));
        $io = $project->getIo();

        $this->assertInstanceOf(MantleOutputStyle::class, $io, 'It should have the default IO.');
    }

    public function test_it_should_set_the_io()
    {
        $io = $this->createMock(MantleOutputStyle::class);

        $project = new Project('./path', $this->createMock(Config::class), $this->createMock(Info::class));
        $project->setIo($io);

        $this->assertSame($io, $project->getIo(), 'It should have the new IO.');
    }

    public function provide_readme_dir_structures(): array
    {
        return [
            'no readme dir' => [
                [
                    '_plugin' => [],
                ],
                null,
            ],
            'no _plugin dir' => [
                [],
                null,
            ],
            'path is a file' => [
                [
                    '_plugin' => [
                        'readme' => '',
                    ],
                ],
                null,
            ],
            'with readme dir' => [
                [
                    '_plugin' => [
                        'readme' => [],
                    ],
                ],
                '_plugin/readme',
            ],
        ];
    }

    /** @dataProvider provide_readme_dir_structures */
    public function test_it_should_get_the_readme_dir_path(array $dirStructure, $expected)
    {
        $vfs = vfsStream::setup('root', null, $dirStructure);

        $project = new Project($vfs->url(), $this->createMock(Config::class), $this->createMock(Info::class));

        $expected = is_string($expected)
            ? $vfs->url() . '/' . $expected
            : $expected;

        $this->assertEquals($expected, $project->getReadmeDirPath());
    }

    public function provide_main_file_template_dir_structures(): array
    {
        return [
            'no _plugin dir' => [
                [],
                null,
            ],
            'no custom file' => [
                [
                    '_plugin' => [],
                ],
                null,
            ],
            'with custom file' => [
                [
                    '_plugin' => [
                        'plugin.php.template' => '',
                    ],
                ],
                '_plugin/plugin.php.template',
            ],
        ];
    }

    /** @dataProvider provide_main_file_template_dir_structures */
    public function test_it_should_get_the_main_file_template_path(array $dirStructure, $expected)
    {
        $vfs = vfsStream::setup('root', null, $dirStructure);

        $project = new Project($vfs->url(), $this->createMock(Config::class), $this->createMock(Info::class));

        $expected = $expected === null
            ? realpath(__DIR__ . '/../../templates/plugin.php.template')
            : $vfs->url() . '/' . $expected;

        $this->assertEquals($expected, $project->getMainFileTemplatePath());
    }

    public function provide_changelog_file_names(): array
    {
        return [
            'changelog.md' => ['changelog.md'],
            'CHANGELOG.md' => ['CHANGELOG.md'],
            'CHANGELOG.MD' => ['CHANGELOG.MD'],
            'CHANGELOG' => ['CHANGELOG'],
        ];
    }

    /** @dataProvider provide_changelog_file_names */
    public function test_it_should_get_the_changelog_path(string $fileName)
    {
        $vfs = vfsStream::setup('root', null, [
            $fileName => '',
        ]);

        $project = new Project($vfs->url(), $this->createMock(Config::class), $this->createMock(Info::class));

        $expected = $vfs->url() . '/' . $fileName;
        $this->assertEquals($expected, $project->getChangelogPath(), 'It should get the changelog path.');
    }

    public function test_it_should_construct_from_array()
    {
        $path = '/path/to/plugin';
        $data = [
            'info' => [
                'name' => 'My Test Plugin',
                'version' => '1.2.3',
                'mainFile' => 'my-plugin.php',
            ],
            'config' => [
                'tempDir' => '/tmp/my-plugin',
                'zipFile' => 'my-plugin.zip',
            ],
            'builds' => [
                'build-1' => [],
                'build-2' => [],
            ],
        ];

        $project = Project::fromArray($path, $data);

        $this->assertSame($path, $project->getPath());
        $this->assertEquals($data['info']['name'], $project->getInfo()->name);
        $this->assertEquals($data['info']['version'], $project->getInfo()->version);
        $this->assertEquals($data['info']['mainFile'], $project->getInfo()->mainFile);
        $this->assertEquals($data['config']['tempDir'], $project->getConfig()->tempDir);
        $this->assertEquals($data['config']['zipFile'], $project->getConfig()->zipFileTemplate);
        $this->assertCount(2, $project->getBuilds());
    }

    public function test_it_should_create_from_json_file()
    {
        $json = /** @lang JSON */
            <<<JSON
                {
                    "info": {
                        "name": "Mantle",
                        "version": "1.2.3",
                        "mainFile": "includes/main.php",
                        "shortId": "mnt",
                        "constantId": "MNT",
                        "description": "A WordPress plugin build tool.",
                        "author": "RebelCode",
                        "authorUrl": "https://rebelcode.com",
                        "textDomain": "mantle",
                        "minWpVer": "5.9",
                        "minPhpVer": "7.1"
                    },
                    "config": {
                        "tempDir": "./tmp/custom-dir",
                        "keepTempDir": true,
                        "zipFile": "build-{{version}}.zip"
                    },
                    "builds": {
                        "free": {
                            "steps": {
                                "copy_files": [
                                    ["add", "includes/free"],
                                    ["add", "includes/plugin.php"],
                                    ["add", "composer.json", "composer.lock"]
                                ],
                                "install_deps": [
                                    ["run", "composer install --no-dev"]
                                ],
                                "clean": [
                                    ["remove", "composer.json", "composer.lock"]
                                ]
                            }
                        },
                        "pro": {
                            "inherits": ["free"],
                            "info": {
                              "name": "Mantle PRO"
                            },
                            "steps": {
                                "+copy_files": [
                                    ["add", "includes/pro"]
                                ]
                            }
                        }
                    }
                }
JSON;

        $vfs = vfsStream::setup('root', null, [
            'build.json' => $json,
        ]);

        $project = Project::fromJsonFile($vfs->url() . '/build.json');

        $info = Project\Info::fromArray([
            'name' => 'Mantle',
            'version' => '1.2.3',
            'mainFile' => 'includes/main.php',
            'shortId' => 'mnt',
            'constantId' => 'MNT',
            'description' => 'A WordPress plugin build tool.',
            'author' => 'RebelCode',
            'authorUrl' => 'https://rebelcode.com',
            'textDomain' => 'mantle',
            'minWpVer' => '5.9',
            'minPhpVer' => '7.1',
        ]);

        $config = new Project\Config([
            'tempDir' => './tmp/custom-dir',
            'keepTempDir' => true,
            'zipFile' => 'build-{{version}}.zip',
        ]);

        $freeBuild = [
            'steps' => [
                'copy_files' => [
                    ['add', 'includes/free'],
                    ['add', 'includes/plugin.php'],
                    ['add', 'composer.json', 'composer.lock'],
                ],
                'install_deps' => [
                    ['run', 'composer install --no-dev'],
                ],
                'clean' => [
                    ['remove', 'composer.json', 'composer.lock'],
                ],
            ],
        ];

        $proBuild = [
            'inherits' => ['free'],
            'info' => [
                'name' => 'Mantle PRO',
            ],
            'steps' => [
                '+copy_files' => [
                    ['add', 'includes/pro'],
                ],
            ],
        ];

        $builds = [
            Build::fromArray('free', $project, $freeBuild),
            Build::fromArray('pro', $project, $proBuild),
        ];

        $this->assertEquals($vfs->url(), $project->getPath());
        $this->assertEquals($info, $project->getInfo());
        $this->assertEquals($config, $project->getConfig());
        $this->assertEquals($builds, $project->getBuilds());
    }

    public function test_it_should_get_development_version()
    {
        $path = './path/to/project';
        $config = new Config();
        $info = new Info('My Test Plugin', '1.2.3', 'my-plugin.php');

        $project = new Project($path, $config, $info);

        $build1Info = new Info('My Test Plugin - Build 1', '1.2.4', 'my-plugin.php');
        $build2Info = new Info('My Test Plugin - Build 2', '1.2.5', 'my-plugin.php');

        $project->setBuilds([
            $build1 = $this->createTestProxy(Build::class, ['first', $project, [], $build1Info]),
            $build2 = $this->createTestProxy(Build::class, ['second', $project, [], $build2Info]),
        ]);

        $ogProject = clone $project;
        $devProject = $project->getForDevelopment();

        $this->assertEquals($ogProject, $project, 'The original project should remain unchanged');

        $this->assertEquals(
            $project->getPath(),
            $devProject->getConfig()->tempDir,
            'The new temp dir should be the same as the project path.'
        );

        $this->assertNotNull(
            $devProject->getBuild('__dev_first__'),
            'The dev project should have a "__dev_first__" build'
        );
        $this->assertNotNull(
            $devProject->getBuild('__dev_second__'),
            'The dev project should have a "__dev_second__" build'
        );

        $this->assertSame(
            $build1->getInfo(),
            $devProject->getBuild('__dev_first__')->getInfo(),
            'The "__dev_first__" build should have the same info as the "first" build'
        );
        $this->assertSame(
            $build2->getInfo(),
            $devProject->getBuild('__dev_second__')->getInfo(),
            'The "__dev_second__" build should have the same info as the "second" build'
        );
    }

    public function test_it_should_get_the_pre_build_step()
    {
        $path = './path/to/my-plugin';
        $config = new Config(['tempDir' => '/tmp/my-plugin']);
        $info = new Info('My Test Plugin', '1.2.3', 'includes/main.php');
        $info->slug = 'my-test-plugin';

        $project = new Project($path, $config, $info);

        $step = $project->getPreBuild();
        $genMainFileInst = $step->getInstructions()[0];

        $this->assertInstanceOf(
            GenerateInstructionType::class,
            $genMainFileInst->getType(),
            'The instruction type should be GenerateInstructionType'
        );

        $this->assertEquals(
            realpath(__DIR__ . '/../../templates/plugin.php.template'),
            $genMainFileInst->getArgs()[0],
            'The template file should be the bundled "templates/plugin.php" file'
        );

        $this->assertEquals(
            'my-test-plugin.php',
            $genMainFileInst->getArgs()[1],
            'The output file should be in the temp dir, using the directory name as a file name'
        );
    }

    public function test_it_should_clean_project()
    {
        $project = new Project(MANTLE_TESTS_DIR, new Config(), new Info('', '', ''), []);
        $project->getConfig()->tempDir = static::TEMP_DIR_PATH;

        mkdir($project->getConfig()->tempDir, 0777, true);
        file_put_contents($project->getConfig()->tempDir . '/test.txt', 'test');

        $project->clean();

        $this->assertDirectoryNotExists($project->getConfig()->tempDir, 'The temp dir should be removed');
    }

    public function test_it_should_build_project()
    {
        $project = new Project(MANTLE_TESTS_DIR, new Config(), new Info('', '', ''), []);
        $project->getConfig()->tempDir = static::TEMP_DIR_PATH;
        $project->getInfo()->slug = 'test';

        $build = $this->createTestProxy(Build::class, ['test', $project]);
        $build->expects($this->once())->method('run')->with([], null);

        $project->addBuild($build);
        $project->build('test');
    }

    public function test_it_should_zip_directory()
    {
        $project = new Project(dirname(static::ZIP_FILE_PATH), new Config(), new Info('', '', ''), []);
        $project->getConfig()->zipFileTemplate = 'build.zip';
        $project->getConfig()->tempDir = MANTLE_TESTS_DIR . '/sample';
        $project->getInfo()->slug = 'test';
        $project->addBuild(new Build('test', $project));
        $project->zip('test');

        $this->assertFileExists(static::ZIP_FILE_PATH);

        $zip = new ZipArchive();
        $zip->open(static::ZIP_FILE_PATH);

        $files = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $files[] = $zip->getNameIndex($i);
        }

        $this->assertContains('test/foo.txt', $files);
        $this->assertContains('test/includes/bar.txt', $files);
        $this->assertContains('test/includes/baz.txt', $files);
    }
}
