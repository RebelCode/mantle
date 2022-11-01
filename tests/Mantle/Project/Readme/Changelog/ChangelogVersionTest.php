<?php

namespace RebelCode\Mantle\Tests\Project\Readme\Changelog;

use PHPUnit\Framework\TestCase;
use RebelCode\Mantle\Project\Readme\Changelog\ChangelogCategory;
use RebelCode\Mantle\Project\Readme\Changelog\ChangelogEntry;
use RebelCode\Mantle\Project\Readme\Changelog\ChangelogVersion;
use Symfony\Component\DomCrawler\Crawler;

class ChangelogVersionTest extends TestCase
{
    public function test_it_should_set_properties()
    {
        $version = new ChangelogVersion(
            $number = '1.0.0',
            $date = '20220-08-12',
            $message = 'Lorem ipsum',
            $categories = [
                new ChangelogCategory('bug', [
                    new ChangelogEntry('1.0.0', 'Message'),
                ]),
            ]
        );

        $this->assertSame($number, $version->number);
        $this->assertSame($date, $version->date);
        $this->assertSame($message, $version->message);
        $this->assertSame($categories, $version->categories);
    }

    public function test_it_should_create_from_crawler_node()
    {
        $number = '1.0.0';
        $date = '2022-08-12';

        $node = new Crawler(
            implode("\n", [
                "<h2 id=\"h2\">$number - $date</h2>",
                '',
                '<h3>Changed</h3>',
                '<ul>',
                '    <li>Lorem ipsum</li>',
                '</ul>',
            ])
        );

        $version = ChangelogVersion::fromCrawlerNode($node->filter('#h2'));

        $this->assertSame($number, $version->number);
        $this->assertSame($date, $version->date);
        $this->assertCount(1, $version->categories);
        $this->assertInstanceOf(ChangelogCategory::class, $version->categories[0]);
        $this->assertSame(ChangelogCategory::CHANGED, $version->categories[0]->type);
        $this->assertCount(1, $version->categories[0]->entries);
        $this->assertInstanceOf(ChangelogEntry::class, $version->categories[0]->entries[0]);
        $this->assertSame('Lorem ipsum', $version->categories[0]->entries[0]->message);
    }

    public function test_it_should_create_from_crawler_node_with_message()
    {
        $number = '1.0.0';
        $date = '2022-08-12';
        $message = 'Best release ever';

        $node = new Crawler(
            implode("\n", [
                "<h2 id=\"h2\">$number - $date</h2>",
                "<p>$message</p>",
                '',
                '<h3>Changed</h3>',
                '<ul>',
                '    <li>Lorem ipsum</li>',
                '</ul>',
            ])
        );

        $version = ChangelogVersion::fromCrawlerNode($node->filter('#h2'));

        $this->assertSame($number, $version->number);
        $this->assertSame($date, $version->date);
        $this->assertSame($message, $version->message);
        $this->assertCount(1, $version->categories);
    }
}
