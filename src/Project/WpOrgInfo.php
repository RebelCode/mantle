<?php

namespace RebelCode\Mantle\Project;

use InvalidArgumentException;

class WpOrgInfo
{
    /** @var string|null */
    public $slug = null;
    /** @var string|null */
    public $name = null;
    /** @var string[] */
    public $tags = [];
    /** @var string[] */
    public $contributors = [];
    /** @var string|null */
    public $testedUpTo = null;

    /**
     * Constructor.
     *
     * @param string|null $slug The slug, or URL ID, of the plugin on WordPress.org.
     * @param string|null $name The human-friendly name of the plugin.
     * @param string|null $testedUpTo The latest version of WordPress that this plugin has been tested with.
     * @param string[] $tags The list of tags for the plugin.
     * @param string[] $contributors The list of contributors for the plugin.
     */
    public function __construct(
        ?string $slug = null,
        ?string $name = null,
        ?string $testedUpTo = null,
        array $tags = [],
        array $contributors = []
    ) {
        $this->slug = $slug;
        $this->name = $name;
        $this->testedUpTo = $testedUpTo;
        $this->tags = $tags;
        $this->contributors = $contributors;
    }

    /** Retrieves the URL of the plugin's page on WordPress.org. */
    public function getUrl(): string
    {
        return "https://wordpress.org/plugins/{$this->slug}/";
    }

    /** Creates an instance from an array. */
    public static function fromArray(array $data): self
    {
        $instance = new self();

        foreach ($data as $key => $value) {
            if (!property_exists($instance, $key)) {
                throw new InvalidArgumentException("Invalid property \"$key\" in wordpress.org meta data");
            }

            $instance->$key = $value;
        }

        return $instance;
    }
}
