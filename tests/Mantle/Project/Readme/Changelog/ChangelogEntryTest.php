<?php

namespace RebelCode\Mantle\Tests\Project\Readme\Changelog;

use RebelCode\Mantle\Project\Readme\Changelog\ChangelogEntry;
use PHPUnit\Framework\TestCase;

class ChangelogEntryTest extends TestCase
{
    public function test_it_should_set_properties()
    {
        $entry = new ChangelogEntry('foo', 'bar');

        $this->assertEquals('foo', $entry->message);
        $this->assertEquals('bar', $entry->tag);
    }

    public function test_it_should_parse_string()
    {
        $entry = ChangelogEntry::parseString('Foo bar');

        $this->assertEquals('Foo bar', $entry->message);
        $this->assertNull($entry->tag);
    }

    public function test_it_should_parse_string_with_tag()
    {
        $entry = ChangelogEntry::parseString('(foo) bar');

        $this->assertEquals('bar', $entry->message);
        $this->assertEquals('foo', $entry->tag);
    }

    public function test_it_should_include_valid_tag()
    {
        $msg = 'bar';
        $tag = 'foo';
        $string = "($tag) $msg";

        $entry = ChangelogEntry::parseString($string, function ($argTag, $argString) use ($tag, $string) {
            $this->assertEquals($tag, $argTag);
            $this->assertEquals($string, $argString);

            return $argTag;
        });

        $this->assertEquals($tag, $entry->tag);
        $this->assertEquals($msg, $entry->message);
    }

    public function test_it_should_include_filtered_tag()
    {
        $msg = 'bar';
        $tag = 'foo';
        $newTag = 'My Tag';
        $string = "($tag) $msg";

        $entry = ChangelogEntry::parseString($string, function ($argTag, $argString) use ($tag, $string, $newTag) {
            $this->assertEquals($tag, $argTag);
            $this->assertEquals($string, $argString);

            return $newTag;
        });

        $this->assertEquals($newTag, $entry->tag);
        $this->assertEquals($msg, $entry->message);
    }

    public function test_it_should_exclude_invalid_tag()
    {
        $msg = 'bar';
        $tag = 'foo';
        $string = "($tag) $msg";

        $entry = ChangelogEntry::parseString($string, function ($argTag, $argString) use ($tag, $string) {
            $this->assertEquals($tag, $argTag);
            $this->assertEquals($string, $argString);

            return null;
        });

        $this->assertNull($entry->tag);
        $this->assertEquals($string, $entry->message);
    }
}
