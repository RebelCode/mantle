<?php

namespace RebelCode\Mantle\Tests\Project\Readme\Changelog;

use RebelCode\Mantle\Project;
use RebelCode\Mantle\Project\Readme\Changelog\ChangelogCategory;
use PHPUnit\Framework\TestCase;
use RebelCode\Mantle\Project\Readme\Changelog\ChangelogEntry;
use Symfony\Component\DomCrawler\Crawler;

class ChangelogCategoryTest extends TestCase
{
    public function test_it_should_set_properties()
    {
        $category = new ChangelogCategory(
            $type = ChangelogCategory::ADDED,
            $entries = [
                $this->createTestProxy(ChangelogEntry::class, ['', '']),
                $this->createTestProxy(ChangelogEntry::class, ['', '']),
            ]
        );

        $this->assertEquals($type, $category->type);
        $this->assertSame($entries, $category->entries);
    }

    public function provide_data_for_get_type_for_string(): array
    {
        return [
            'added' => ['added', ChangelogCategory::ADDED],
            'changed' => ['changed', ChangelogCategory::CHANGED],
            'removed' => ['removed', ChangelogCategory::REMOVED],
            'fixed' => ['fixed', ChangelogCategory::FIXED],
            'deprecated' => ['deprecated', ChangelogCategory::DEPRECATED],
            'security' => ['security', ChangelogCategory::SECURITY],
            'unknown' => ['unknown', null],
        ];
    }

    /** @dataProvider provide_data_for_get_type_for_string */
    public function test_it_should_get_type_for_string(string $input, $expected)
    {
        $this->assertEquals($expected, ChangelogCategory::getTypeForString($input));
    }

    public function test_it_should_parse_from_crawler_node()
    {
        $node = new Crawler(
            <<<HTML
            <h2 id="h2">Added</h2>

            <ul>
                <li>Lorem ipsum</li>
                <li>Dolor sit amet</li>
            </ul>
            HTML
        );

        $category = ChangelogCategory::fromCrawlerNode($node->filter('#h2'));

        $this->assertEquals(ChangelogCategory::ADDED, $category->type);
        $this->assertCount(2, $category->entries);
        $this->assertEquals('Lorem ipsum', $category->entries[0]->message);
        $this->assertEquals('Dolor sit amet', $category->entries[1]->message);
    }
}
