<?php

namespace RebelCode\Mantle\InstructionType;

use InvalidArgumentException;
use RebelCode\Mantle\InstructionType;
use RebelCode\Mantle\MantleOutputStyle;
use RebelCode\Mantle\Project;
use RuntimeException;

/** Instructions that generate files and add them to the build. */
class GenerateFiles implements InstructionType
{
    public function run(Project\Build $build, array $args, MantleOutputStyle $io): void
    {
        $numArgs = count($args);

        if ($numArgs < 1) {
            throw new InvalidArgumentException('Missing 1st argument (the input file) for "GENERATE" instruction');
        } elseif ($numArgs < 2) {
            throw new InvalidArgumentException('Missing 2nd argument (the output file) for "GENERATE" instruction');
        } elseif ($numArgs > 2) {
            throw new InvalidArgumentException('Too many arguments for "GENERATE" instruction');
        }

        $templateFile = $args[0];
        $outputFile = $args[1];

        $project = $build->getProject();

        $inPath = file_exists($templateFile)
            ? $templateFile
            : $project->getPath() . '/' . $templateFile;

        $outPath = $project->getConfig()->buildDir . '/' . $outputFile;

        if (!file_exists($inPath)) {
            throw new RuntimeException("Template file does not exist: {$inPath}");
        } elseif (is_dir($inPath)) {
            throw new RuntimeException("Template file path is a directory: {$inPath}");
        } elseif (!is_readable($inPath)) {
            throw new RuntimeException("Template file is not readable: {$inPath}");
        } else {
            $template = file_get_contents($inPath);
            $newContent = $build->interpolate($template);
            file_put_contents($outPath, $newContent);

            $io->writeInstruction('magenta', 'Generated %s', $outPath);
        }
    }
}
