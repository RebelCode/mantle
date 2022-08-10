<?php

namespace RebelCode\Mantle\Tests\Project;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RebelCode\Mantle\Project\Info;
use RebelCode\Mantle\Project\WpOrgInfo;

class InfoTest extends TestCase
{
    public function test_it_should_construct()
    {
        $name = 'My Test Plugin';
        $version = '1.2.3';
        $mainFile = 'my-plugin.php';

        $info = new Info($name, $version, $mainFile);

        $this->assertSame($name, $info->name);
        $this->assertSame($version, $info->version);
        $this->assertSame($mainFile, $info->mainFile);
        $this->assertSame('my-test-plugin', $info->slug);
        $this->assertSame('mtp', $info->shortId);
        $this->assertSame('MTP', $info->constantId);
        $this->assertEquals('default', $info->textDomain);
        $this->assertEquals('/languages', $info->domainPath);
        $this->assertNull($info->description);
        $this->assertNull($info->url);
        $this->assertNull($info->author);
        $this->assertNull($info->authorUrl);
        $this->assertNull($info->minWpVer);
        $this->assertNull($info->minPhpVer);
        $this->assertNull($info->license);
        $this->assertNull($info->wpOrgInfo);
    }

    public function test_it_should_construct_from_array()
    {
        $array = [
            'name' => 'Test Plugin',
            'version' => '1.0.0',
            'mainFile' => 'my-plugin.php',
            'slug' => 'test-plugin',
            'shortId' => 'test',
            'constantId' => 'TEST',
            'description' => 'Test plugin description',
            'url' => 'https://example.com',
            'author' => 'John Doe',
            'authorUrl' => 'https://example.com/author',
            'textDomain' => 'test-plugin',
            'domainPath' => '/my-domain',
            'minWpVer' => '4.0',
            'minPhpVer' => '7.0',
            'license' => 'GPLv3',
            'wpOrg' => [
                'slug' => 'Test Plugin - All your testing needs',
                'testedUpTo' => '6.0',
                'tags' => ['wordpress', 'plugin', 'test'],
                'contributors' => ['John Doe', 'Jane Doe'],
            ],
        ];

        $info = Info::fromArray($array);

        $this->assertSame($array['name'], $info->name);
        $this->assertSame($array['slug'], $info->slug);
        $this->assertSame($array['shortId'], $info->shortId);
        $this->assertSame($array['constantId'], $info->constantId);
        $this->assertSame($array['description'], $info->description);
        $this->assertSame($array['version'], $info->version);
        $this->assertSame($array['url'], $info->url);
        $this->assertSame($array['author'], $info->author);
        $this->assertSame($array['authorUrl'], $info->authorUrl);
        $this->assertSame($array['textDomain'], $info->textDomain);
        $this->assertSame($array['domainPath'], $info->domainPath);
        $this->assertSame($array['minWpVer'], $info->minWpVer);
        $this->assertSame($array['minPhpVer'], $info->minPhpVer);

        $this->assertInstanceOf(WpOrgInfo::class, $info->wpOrgInfo);
        $this->assertEquals($array['wpOrg']['slug'], $info->wpOrgInfo->slug);
        $this->assertEquals($array['wpOrg']['testedUpTo'], $info->wpOrgInfo->testedUpTo);
        $this->assertEquals($array['wpOrg']['tags'], $info->wpOrgInfo->tags);
        $this->assertEquals($array['wpOrg']['contributors'], $info->wpOrgInfo->contributors);
    }

    public function test_it_should_throw_if_array_is_missing_the_name()
    {
        $this->expectException(InvalidArgumentException::class);

        Info::fromArray([
            'version' => '1.2.3',
            'mainFile' => 'my-plugin.php',
        ]);
    }

    public function test_it_should_throw_if_array_is_missing_the_version()
    {
        $this->expectException(InvalidArgumentException::class);

        Info::fromArray([
            'name' => 'My Test Plugin',
            'mainFile' => 'my-plugin.php',
        ]);
    }

    public function test_it_should_throw_if_array_is_missing_the_main_file()
    {
        $this->expectException(InvalidArgumentException::class);

        Info::fromArray([
            'name' => 'My Test Plugin',
            'version' => '1.2.3',
        ]);
    }
}
