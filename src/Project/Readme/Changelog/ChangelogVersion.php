<?php

namespace RebelCode\Mantle\Project\Readme\Changelog;

use RebelCode\Mantle\Project;
use RuntimeException;
use Symfony\Component\DomCrawler\Crawler;

class ChangelogVersion
{
    /** @var string */
    public $number;
    /** @var string */
    public $date;
    /** @var string */
    public $message;
    /** @var ChangelogCategory[] */
    public $categories;

    /**
     * Constructor.
     *
     * @param string $number The version number.
     * @param string $date The date of the version, in YYYY-MM-DD format.
     * @param string $message Optional message for the version.
     * @param ChangelogCategory[] $categories The version categories.
     */
    public function __construct(string $number, string $date, string $message = '', array $categories = [])
    {
        $this->number = $number;
        $this->date = $date;
        $this->message = $message;
        $this->categories = $categories;
    }

    /**
     * Parses a changelog version from an <h2> Crawler node.
     *
     * @param Crawler $h2 The <h2> node.
     * @param Project|null $project The project. If given, it will be used to filter tags in changelog entries.
     *                              See {@link ChangelogEntry::parseString()} for more information.
     */
    public static function fromCrawlerNode(Crawler $h2, ?Project $project = null): self
    {
        $text = $h2->innerText();

        $parts = explode('-', $text, 2);
        $number = trim($parts[0] ?? '');
        $date = trim($parts[1] ?? '');

        if (empty($number)) {
            throw new RuntimeException('Invalid version number in changelog: ' . $text);
        }

        if (preg_match('/^\[.+]$/', $number)) {
            $number = trim(substr($number, 1, -1));
        }

        if (strtolower($number) !== 'unreleased') {
            if (empty($date)) {
                throw new RuntimeException('Missing version date in changelog: ' . $text);
            }

            $dateParts = explode('-', $date);
            if (count($dateParts) !== 3 || !checkdate($dateParts[1], $dateParts[2], $dateParts[0])) {
                throw new RuntimeException('Invalid version date in changelog: ' . $date);
            }
        }

        $pNode = $h2->nextAll()->filter('p')->first();
        $message = $pNode->text('');

        $categories = $h2->nextAll()->filter('h3')->each(function (Crawler $h3) use ($project) {
            return ChangelogCategory::fromCrawlerNode($h3, $project);
        });

        return new ChangelogVersion($number, $date, $message, $categories);
    }
}
