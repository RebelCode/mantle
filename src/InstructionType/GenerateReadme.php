<?php

namespace RebelCode\Mantle\InstructionType;

use RebelCode\Mantle\InstructionType;
use RebelCode\Mantle\MantleOutputStyle;
use RebelCode\Mantle\Project;

/** Internal instruction type to generate a readme.txt file for plugins that are hosted on wordpress.org */
class GenerateReadme implements InstructionType
{
    public function run(Project\Build $build, array $args, MantleOutputStyle $io): void
    {
        $project = $build->getProject();

        $outPath = $project->getConfig()->buildDir . '/readme.txt';
        $readme = Project\Readme::fromFilesInDir($project->getReadmeDirPath());

        file_put_contents($outPath, $readme->render($project));
    }
}
