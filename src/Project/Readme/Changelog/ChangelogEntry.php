<?php

namespace RebelCode\Mantle\Project\Readme\Changelog;

class ChangelogEntry
{
    protected const TAG_PATTERN = '/^\(([a-zA-Z_]+)\)\s+?(.*)$/';
    /** @var string */
    public $message;
    /** @var string|null */
    public $tag;

    /**
     * Constructor.
     *
     * @param string $message The message of the entry.
     * @param string|null $tag Optional string associated with the message. This is used to later cherry-pick entries.
     */
    public function __construct(string $message, ?string $tag = null)
    {
        $this->message = $message;
        $this->tag = $tag;
    }

    /**
     * Parses a string into an instance.
     *
     * @param string $string The string to parse.
     * @param callable|null $tagFilter Optional callback to validate tags. If omitted, all tags will be accepted. The
     *                                 callback will be passed the tag and the full string, and should return the tag
     *                                 string to use, if the tag is valid, or null if the tag is invalid. Invalid tags
     *                                 will result in an entry with a null tag and the full string as the message.
     * @return self The created instance.
     */
    public static function parseString(string $string, ?callable $tagFilter = null): self
    {
        $message = trim($string);
        $tag = null;

        if (preg_match(static::TAG_PATTERN, $message, $matches) && count($matches) >= 3) {
            $tag = $tagFilter ? $tagFilter($matches[1], $message) : $matches[1];

            if ($tag !== null) {
                $message = $matches[2];
            }
        }

        return new ChangelogEntry($message, $tag);
    }
}
