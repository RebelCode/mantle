<?php

namespace RebelCode\Mantle\Project;

use InvalidArgumentException;

class Info
{
    /** @var string */
    public $name;
    /** @var string */
    public $version;
    /** @var string */
    public $mainFile;
    /** @var string */
    public $slug;
    /** @var string */
    public $shortId;
    /** @var string */
    public $constantId;
    /** @var string|null */
    public $description = null;
    /** @var string|null */
    public $url = null;
    /** @var string|null */
    public $author = null;
    /** @var string|null */
    public $authorUrl = null;
    /** @var string */
    public $textDomain = 'default';
    /** @var string */
    public $domainPath = '/languages';
    /** @var string|null */
    public $minWpVer = null;
    /** @var string|null */
    public $minPhpVer = null;
    /** @var string|null */
    public $license = null;
    /** @var WpOrgInfo */
    public $wpOrg = null;

    /**
     * Constructor.
     *
     * @param string $name The human-friendly name of the plugin.
     * @param string $version The version of the plugin.
     * @param string $mainFile The path to the main plugin file, relative to the project root directory.
     */
    public function __construct(string $name, string $version, string $mainFile)
    {
        $this->name = $name;
        $this->version = $version;
        $this->mainFile = $mainFile;
        $this->slug = static::generateSlug($name);
        $this->shortId = static::generateShortId($name);
        $this->constantId = strtoupper($this->shortId);
    }

    /** Creates an instance from an array of data. */
    public static function fromArray(array $data): self
    {
        foreach (['name', 'version', 'mainFile'] as $prop) {
            if (!array_key_exists($prop, $data)) {
                throw new InvalidArgumentException("Missing required property \"$prop\" in plugin meta data");
            }
        }

        $info = new self($data['name'], $data['version'], $data['mainFile']);

        if (array_key_exists('wpOrg', $data)) {
            $info->wpOrg = WpOrgInfo::fromArray($data['wpOrg']);
            unset($data['wpOrg']);
        }

        $info->addData($data);

        return $info;
    }

    /** Adds data to the instance from an array. */
    public function addData(array $data): self
    {
        foreach ($data as $key => $value) {
            if (!property_exists($this, $key)) {
                throw new InvalidArgumentException("Invalid property \"$key\" in plugin meta data");
            }

            $this->$key = $value;
        }

        return $this;
    }

    public function toArray(): array
    {
        $array = [];
        foreach (get_class_vars(self::class) as $prop => $default) {
            $array[$prop] = $this->$prop ?? $default;
        }

        return $array;
    }

    /** Generates a slug for a plugin based on its name. */
    public static function generateSlug(string $name): string
    {
        return strtolower(str_replace(' ', '-', str_replace('-', '', $name)));
    }

    /** Generates a short ID for a plugin based on its name. */
    public static function generateShortId(string $name): string
    {
        $words = array_filter(explode(' ', $name));
        $initials = array_map(function ($word) {
            return $word[0];
        }, $words);

        return strtolower(implode('', $initials));
    }
}
