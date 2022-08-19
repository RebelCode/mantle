<?php

namespace RebelCode\Mantle\Tests\Svn;

use RebelCode\Mantle\Svn\SvnStatus;
use PHPUnit\Framework\TestCase;
use RebelCode\Mantle\Svn\SvnStatusEntry;

class SvnStatusTest extends TestCase
{
    public function test_it_should_set_properties()
    {
        $path = 'path';
        $entries = [
            new SvnStatusEntry('A', 'path/to/foo'),
            new SvnStatusEntry('M', 'path/to/bar'),
        ];

        $status = new SvnStatus($path, $entries);

        $this->assertSame($path, $status->getPath());
        $this->assertSame($entries, $status->getEntries());
    }

    public function test_it_should_get_total_count()
    {
        $status = new SvnStatus('path', [
            new SvnStatusEntry('A', 'path/to/foo'),
            new SvnStatusEntry('M', 'path/to/bar'),
            new SvnStatusEntry('D', 'path/to/baz'),
        ]);

        $this->assertSame(3, $status->getCount());
    }

    public function test_it_should_get_count_for_type()
    {
        $status = new SvnStatus('path', [
            new SvnStatusEntry('A', 'path/to/A1'),
            new SvnStatusEntry('A', 'path/to/A2'),
            new SvnStatusEntry('A', 'path/to/A3'),
            new SvnStatusEntry('M', 'path/to/M1'),
            new SvnStatusEntry('D', 'path/to/D1'),
            new SvnStatusEntry('D', 'path/to/D2'),
        ]);

        $this->assertSame(3, $status->getCount('A'));
        $this->assertSame(1, $status->getCount('M'));
        $this->assertSame(2, $status->getCount('D'));
    }

    public function test_it_should_get_entry_for_path()
    {
        $status = new SvnStatus('path', [
            $A1 = new SvnStatusEntry('A', 'path/to/A1'),
            $A2 = new SvnStatusEntry('A', 'path/to/A2'),
            $A3 = new SvnStatusEntry('A', 'path/to/A3'),
            $M1 = new SvnStatusEntry('M', 'path/to/M1'),
            $D1 = new SvnStatusEntry('D', 'path/to/D1'),
            $D2 = new SvnStatusEntry('D', 'path/to/D2'),
        ]);

        $this->assertSame($A1, $status->getEntryForPath('path/to/A1'));
        $this->assertSame($A2, $status->getEntryForPath('path/to/A2'));
        $this->assertSame($A3, $status->getEntryForPath('path/to/A3'));
        $this->assertSame($M1, $status->getEntryForPath('path/to/M1'));
        $this->assertSame($D1, $status->getEntryForPath('path/to/D1'));
        $this->assertSame($D2, $status->getEntryForPath('path/to/D2'));
    }

    public function test_it_should_get_no_entry_for_path_with_no_match()
    {
        $status = new SvnStatus('path', [
            new SvnStatusEntry('A', 'path/to/A1'),
            new SvnStatusEntry('M', 'path/to/M1'),
            new SvnStatusEntry('D', 'path/to/D1'),
        ]);

        $this->assertNull($status->getEntryForPath('path/to/foo'));
    }
}
