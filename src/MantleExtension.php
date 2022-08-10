<?php

namespace RebelCode\Mantle;

use RebelCode\Mantle\Project\Build;

interface MantleExtension
{
    /**
     * Runs when the project is being initialized.
     * Use this method to register new instructions, alter configuration, etc.
     */
    public function init(Project $project): void;

    /**
     * Runs before the project is built.
     * Use this method to perform pre-build tasks, such as generating files, preparing requisites for custom
     * instructions, or altering the build's configuration.
     */
    public function beforeBuild(Project $project, Build $build): void;

    /**
     * Runs after the project is built.
     * Use this method to perform post-build task, such as additional steps, cleanup, or integrating with a service.
     */
    public function afterBuild(Project $project, Build $build): void;

    /**
     * Runs before a build is run for development.
     * This is called _before_ the {@link MantleExtension::beforeBuild} method.
     */
    public function beforeDev(Project $project, Build $build): void;

    /**
     * Runs before a build is run for development.
     * This is called _after_ the {@link MantleExtension::afterBuild} method.
     */
    public function afterDev(Project $project, Build $build): void;
}
