<?php

namespace RebelCode\Mantle\Tests\Svn;

use RebelCode\Mantle\Project;
use RebelCode\Mantle\Project\Build;
use RebelCode\Mantle\Svn\SvnConfig;
use PHPUnit\Framework\TestCase;

class SvnConfigTest extends TestCase
{
    public function test_it_should_set_properties()
    {
        $build = 'my_build';
        $trunkCommit = 'My trunk commit message';
        $tagCommit = 'My tag commit message';
        $checkoutDir = './svn';

        $config = new SvnConfig($build, $trunkCommit, $tagCommit, $checkoutDir);

        $this->assertEquals($build, $config->build);
        $this->assertEquals($trunkCommit, $config->trunkCommit);
        $this->assertEquals($tagCommit, $config->tagCommit);
        $this->assertEquals($checkoutDir, $config->checkoutDir);
    }

    public function test_it_should_use_defaults()
    {
        $config = new SvnConfig('');

        $this->assertEquals('', $config->build);
        $this->assertEquals('Update trunk to v{{version}}', $config->trunkCommit);
        $this->assertEquals('Add tag {{version}}', $config->tagCommit);
        $this->assertNull($config->checkoutDir);
    }

    public function test_it_should_create_from_array()
    {
        $array = [
            'build' => 'my_build',
            'trunkCommit' => 'My trunk commit message',
            'tagCommit' => 'My tag commit message',
            'checkoutDir' => './svn',
        ];

        $config = SvnConfig::fromArray($array);

        $this->assertEquals($array['build'], $config->build);
        $this->assertEquals($array['trunkCommit'], $config->trunkCommit);
        $this->assertEquals($array['tagCommit'], $config->tagCommit);
        $this->assertEquals($array['checkoutDir'], $config->checkoutDir);
    }

    public function test_it_should_get_trunk_commit_message()
    {
        $config = new SvnConfig('', 'Update {{name}} trunk to {{version}}');

        $project = $this->createMock(Project::class);
        $build = new Build('test', $project, [], Project\Info::fromArray([
            'name' => 'My Plugin',
            'mainFile' => 'main.php',
            'version' => '1.2.3',
        ]));

        $this->assertEquals('Update My Plugin trunk to 1.2.3', $config->getTrunkCommitMsg($build));
    }

    public function test_it_should_get_tag_commit_message()
    {
        $config = new SvnConfig('', '', 'Add {{name}} tag {{version}}');

        $project = $this->createMock(Project::class);
        $build = new Build('test', $project, [], Project\Info::fromArray([
            'name' => 'My Plugin',
            'mainFile' => 'main.php',
            'version' => '1.2.3',
        ]));

        $this->assertEquals('Add My Plugin tag 1.2.3', $config->getTagCommitMsg($build));
    }
}
