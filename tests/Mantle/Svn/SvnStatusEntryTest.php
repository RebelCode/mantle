<?php

namespace RebelCode\Mantle\Tests\Svn;

use InvalidArgumentException;
use RebelCode\Mantle\Svn\SvnStatusEntry;
use PHPUnit\Framework\TestCase;

class SvnStatusEntryTest extends TestCase
{
    public function test_it_should_set_properties()
    {
        $type = 'A';
        $path = 'foo/bar.php';

        $entry = new SvnStatusEntry($type, $path);

        $this->assertSame($type, $entry->getType());
        $this->assertSame($path, $entry->getPath());
    }

    public function test_it_should_create_from_line()
    {
        $entry = SvnStatusEntry::fromSvnStatusLine('A     foo/bar.php');

        $this->assertSame('A', $entry->getType());
        $this->assertSame('foo/bar.php', $entry->getPath());
    }

    public function test_it_should_throw_exception_if_line_is_empty()
    {
        $this->expectException(InvalidArgumentException::class);

        SvnStatusEntry::fromSvnStatusLine('');
    }
}
