<?php

namespace RebelCode\Mantle\Svn;

use InvalidArgumentException;
use RebelCode\Mantle\Project\Build;

/** Configuration for the WordPress.org SVN repository.  */
class SvnConfig
{
    /** @var string */
    public $build;
    /** @var string */
    public $trunkCommit = 'Update trunk to v{{version}}';
    /** @var string */
    public $tagCommit = 'Add tag {{version}}';
    /** @var string|null */
    public $checkoutDir = '.wporg';

    /**
     * Constructor.
     *
     * @param string $build The name of the build to be published to the WordPress.org SVN repository.
     * @param string|null $trunkCommit The commit message to use when updating the trunk.
     * @param string|null $tagCommit The commit message to use when adding a tag.
     * @param string|null $checkoutDir The path to the directory to use to checkout the repository.
     */
    public function __construct(
        string $build,
        ?string $trunkCommit = null,
        ?string $tagCommit = null,
        ?string $checkoutDir = null
    ) {
        $this->build = $build;
        $this->trunkCommit = $trunkCommit ?? $this->trunkCommit;
        $this->tagCommit = $tagCommit ?? $this->tagCommit;
        $this->checkoutDir = $checkoutDir;
    }

    /** Retrieves the trunk commit message, with any placeholders replaced. */
    public function getTrunkCommitMsg(Build $build): string
    {
        return $build->interpolate($this->trunkCommit);
    }

    /** Retrieves the tag commit message, with any placeholders replaced.  */
    public function getTagCommitMsg(Build $build): string
    {
        return $build->interpolate($this->tagCommit);
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
