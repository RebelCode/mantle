<?php

namespace RebelCode\Mantle\Project;

use InvalidArgumentException;

/** Configuration for the WordPress.org SVN repository.  */
class SvnConfig
{
    /** @var string */
    public $build;
    /** @var string */
    public $trunkCommitMessage = 'Update trunk to v{{version}}';
    /** @var string */
    public $tagCommitMessage = 'Add tag {{version}}';
    /** @var bool */
    public $autoStableTag = false;
    /** @var string|null */
    public $checkoutDir = null;

    /**
     * Constructor.
     *
     * @param string $build The name of the build to be published to the WordPress.org SVN repository.
     * @param string|null $trunkCommitMessage The commit message to use when updating the trunk.
     * @param string|null $tagCommitMessage The commit message to use when adding a tag.
     * @param bool $autoStableTag Whether to automatically create a stable tag when a new stable version is published.
     * @param string|null $checkoutDir The path to the directory to use to checkout the repository.
     */
    public function __construct(
        string $build,
        ?string $trunkCommitMessage = null,
        ?string $tagCommitMessage = null,
        bool $autoStableTag = false,
        ?string $checkoutDir = null
    ) {
        $this->build = $build;
        $this->trunkCommitMessage = $trunkCommitMessage ?? $this->trunkCommitMessage;
        $this->tagCommitMessage = $tagCommitMessage ?? $this->tagCommitMessage;
        $this->autoStableTag = $autoStableTag;
        $this->checkoutDir = $checkoutDir;
    }

    /** Retrieves the trunk commit message, with any placeholders replaced. */
    public function getTrunkCommitMsg(Build $build): string
    {
        return $build->interpolate($this->trunkCommitMessage);
    }

    /** Retrieves the tag commit message, with any placeholders replaced.  */
    public function getTagCommitMsg(Build $build): string
    {
        return $build->interpolate($this->tagCommitMessage);
    }

    /**
     * Creates an instance from an array.
     *
     * @param array $data The data to use to create the instance.
     * @return SvnConfig
     */
    public static function fromArray(array $data): self
    {
        if (!array_key_exists('build', $data)) {
            throw new InvalidArgumentException('Missing "build" key in SVN config');
        }

        $instance = new self($data['build']);
        unset($data['build']);

        foreach ($data as $key => $value) {
            if (!property_exists($instance, $key)) {
                throw new InvalidArgumentException("Invalid property \"$key\" in svn config");
            }

            $instance->$key = $value;
        }

        return $instance;
    }
}
