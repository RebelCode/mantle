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
        $trunkCommitMessage = 'My trunk commit message';
        $tagCommitMessage = 'My tag commit message';
        $autoStableTag = true;
        $checkoutDir = './svn';

        $config = new SvnConfig($build, $trunkCommitMessage, $tagCommitMessage, $autoStableTag, $checkoutDir);

        $this->assertEquals($build, $config->build);
        $this->assertEquals($trunkCommitMessage, $config->trunkCommitMessage);
        $this->assertEquals($tagCommitMessage, $config->tagCommitMessage);
        $this->assertEquals($autoStableTag, $config->autoStableTag);
        $this->assertEquals($checkoutDir, $config->checkoutDir);
    }

    public function test_it_should_use_defaults()
    {
        $config = new SvnConfig('');

        $this->assertEquals('', $config->build);
        $this->assertEquals('Update trunk to v{{version}}', $config->trunkCommitMessage);
        $this->assertEquals('Add tag {{version}}', $config->tagCommitMessage);
        $this->assertEquals(false, $config->autoStableTag);
        $this->assertNull($config->checkoutDir);
    }

    public function test_it_should_create_from_array()
    {
        $array = [
            'build' => 'my_build',
            'trunkCommitMessage' => 'My trunk commit message',
            'tagCommitMessage' => 'My tag commit message',
            'autoStableTag' => true,
            'checkoutDir' => './svn',
        ];

        $config = SvnConfig::fromArray($array);

        $this->assertEquals($array['build'], $config->build);
        $this->assertEquals($array['trunkCommitMessage'], $config->trunkCommitMessage);
        $this->assertEquals($array['tagCommitMessage'], $config->tagCommitMessage);
        $this->assertEquals($array['autoStableTag'], $config->autoStableTag);
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
