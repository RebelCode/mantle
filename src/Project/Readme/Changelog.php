<?php

namespace RebelCode\Mantle\Project\Readme;

use League\CommonMark\CommonMarkConverter;
use RebelCode\Mantle\Project;
use RebelCode\Mantle\Project\Readme\Changelog\ChangelogEntry;
use RebelCode\Mantle\Project\Readme\Changelog\ChangelogVersion;
use Symfony\Component\DomCrawler\Crawler;

class Changelog
{
    /** @var ChangelogVersion[] */
    public $versions = [];

    /**
     * Constructor.
     *
     * @param ChangelogVersion[] $versions The version entries in the changelog.
     */
    public function __construct(array $versions)
    {
        $this->versions = $versions;
    }

    /** Renders the changelog in WordPress.org format. */
    public function toWpOrgFormat(array $tags = [], bool $includeTags = true): string
    {
        $lines = [];
        foreach ($this->versions as $version) {
            $lines[] = "### {$version->number}" . ($version->date ? " ({$version->date})" : '');

            if (!empty($version->message)) {
                $lines[] = $version->message;
            }

            $didCategory = false;
            foreach ($version->categories as $category) {
                $entries = array_filter($category->entries, function (ChangelogEntry $entry) use ($tags) {
                    return count($tags) === 0 || in_array($entry->tag ?? '', $tags);
                });

                if (count($entries) > 0) {
                    $didCategory = true;
                    $categoryText = ucfirst($category->type);
                    $lines[] = "#### {$categoryText}";

                    foreach ($entries as $entry) {
                        if ($entry->tag !== null && $includeTags) {
                            $lines[] = "- ({$entry->tag}) {$entry->message}";
                        } else {
                            $lines[] = "- {$entry->message}";
                        }
                    }

                    if (count($entries)) {
                        $lines[] = '';
                    }
                }
            }

            if (!$didCategory) {
                $lines[] = '';
            }
        }

        return trim(implode("\n", $lines));
    }

    /**
     * Parses a markdown changelog file that follows the [Keep a Changelog](https://keepachangelog.com/) format.
     *
     * @param string $markdown The markdown string.
     * @param Project|null $project The project. If given, the build names in changelog entries will be parsed and used
     *                              as entry tags.
     * @return self The parsed changelog.
     */
    public static function parseMarkdown(string $markdown, ?Project $project = null): self
    {
        $converter = new CommonMarkConverter(['html_input' => 'strip']);
        $html = $converter->convertToHtml($markdown);

        // Wrap each version in a div for easier crawling
        $html = '<div>' . $html . '</div>';
        $html = str_replace('<h2>', '</div><div><h2>', $html);

        $crawler = new Crawler($html);

        $versions = $crawler->filter('h2')->each(function (Crawler $h2) use ($project) {
            return ChangelogVersion::fromCrawlerNode($h2, $project);
        });

        return new self($versions);
    }
}
