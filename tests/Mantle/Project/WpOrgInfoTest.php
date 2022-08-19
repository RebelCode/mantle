<?php

namespace RebelCode\Mantle\Tests\Project;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RebelCode\Mantle\Project\WpOrgInfo;

class WpOrgInfoTest extends TestCase
{
    public function test_it_should_construct()
    {
        $slug = 'my-test-plugin';
        $name = 'My Test Plugin';
        $testedUpTo = '4.0';
        $tags = ['tag1', 'tag2'];
        $contributors = ['contributor1', 'contributor2'];

        $info = new WpOrgInfo($slug, $name, $testedUpTo, $tags, $contributors);

        $this->assertSame($slug, $info->slug);
        $this->assertSame($name, $info->name);
        $this->assertSame($testedUpTo, $info->testedUpTo);
        $this->assertSame($tags, $info->tags);
        $this->assertSame($contributors, $info->contributors);
    }

    public function test_it_should_construct_from_array()
    {
        $array = [
            'slug' => 'test-plugin',
            'name' => 'Test Plugin',
            'testedUpTo' => '4.0',
            'tags' => ['tag1', 'tag2'],
            'contributors' => ['contributor1', 'contributor2'],
        ];

        $info = WpOrgInfo::fromArray($array);

        $this->assertSame($array['slug'], $info->slug);
        $this->assertSame($array['name'], $info->name);
        $this->assertSame($array['testedUpTo'], $info->testedUpTo);
        $this->assertSame($array['tags'], $info->tags);
        $this->assertSame($array['contributors'], $info->contributors);
    }

    public function test_it_should_construct_from_array_with_defaults()
    {
        $info = WpOrgInfo::fromArray([]);

        $this->assertNull($info->slug);
        $this->assertNull($info->name);
        $this->assertNull($info->testedUpTo);
        $this->assertEquals([], $info->tags);
        $this->assertEquals([], $info->contributors);
    }

    public function test_it_should_throw_exception_with_unknown_array_key()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/"unknown"/');

        $array = [
            'slug' => 'test-plugin',
            'name' => 'Test Plugin',
            'testedUpTo' => '4.0',
            'tags' => ['tag1', 'tag2'],
            'contributors' => ['contributor1', 'contributor2'],
            'unknown' => 'value',
        ];

        WpOrgInfo::fromArray($array);
    }
}
