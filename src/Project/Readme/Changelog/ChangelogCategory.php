<?php

namespace RebelCode\Mantle\Project\Readme\Changelog;

use RebelCode\Mantle\Project;
use Symfony\Component\DomCrawler\Crawler;

class ChangelogCategory
{
    public const ADDED = 'added';
    public const CHANGED = 'changed';
    public const REMOVED = 'removed';
    public const FIXED = 'fixed';
    public const DEPRECATED = 'deprecated';
    public const SECURITY = 'security';
    /** @var string */
    public $type;
    /** @var ChangelogEntry[] */
    public $entries;

    /**
     * Constructor.
     *
     * @param int $type The category. See the constants on this class.
     * @param ChangelogEntry[] $entries The entries in the category.
     */
    public function __construct(string $type, array $entries)
    {
        $this->type = $type;
        $this->entries = $entries;
    }

    /** Gets a category type from a string. */
    public static function getTypeForString(string $string): ?string
    {
        switch (strtolower($string)) {
            case 'added':
                return ChangelogCategory::ADDED;
            case 'changed':
                return ChangelogCategory::CHANGED;
            case 'removed':
                return ChangelogCategory::REMOVED;
            case 'fixed':
                return ChangelogCategory::FIXED;
            case 'deprecated':
                return ChangelogCategory::DEPRECATED;
            case 'security':
                return ChangelogCategory::SECURITY;
            default:
                return null;
        }
    }

    /**
     * Parses a changelog category from an <h3> Crawler node.
     *
     * @param Crawler $h3 The <h3> node.
     * @param Project|null $project The project. If given, it will be used to filter tags in changelog entries.
     *                              See {@link ChangelogEntry::parseString()} for more information.
     */
    public static function fromCrawlerNode(Crawler $h3, ?Project $project = null): self
    {
        $type = ChangelogCategory::getTypeForString($h3->text(''));

        $listItems = $h3->nextAll()->filter('ul')->first()->filter('li');

        $entries = $listItems->each(function (Crawler $li) use ($project) {
            $tagFilter = $project ? function ($tag) use ($project) {
                $build = $project->getBuild($tag);
                return $build ? $build->getName() : null;
            } : null;

            return ChangelogEntry::parseString($li->text(''), $tagFilter);
        });

        return new ChangelogCategory($type, $entries);
    }
}
