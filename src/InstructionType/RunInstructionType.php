<?php

namespace RebelCode\Mantle\InstructionType;

use InvalidArgumentException;
use RebelCode\Mantle\InstructionType;
use RebelCode\Mantle\Project;
use RuntimeException;

/** Instructions that run a shell command or external program. */
class RunInstructionType implements InstructionType
{
    /** @inheritDoc */
    public function run(Project\Build $build, array $args): void
    {
        $numArgs = count($args);

        if ($numArgs === 0) {
            throw new InvalidArgumentException('Missing argument for "RUN" instruction');
        } elseif ($numArgs > 2) {
            throw new InvalidArgumentException('Too many arguments for "RUN" instruction');
        }

        $cwd = ($numArgs === 2) ? $args[0] : null;
        $command = $args[$numArgs - 1];

        // Save the current working directory
        $prevCwd = getcwd();
        // Change the working directory if necessary
        if ($cwd !== null) {
            chdir($cwd);
        }

        $io = $build->getProject()->getIo();
        $io->writeInstruction('yellow', 'Running %s', $command);

        if ($io->isVerbose()) {
            passthru($command, $code);
        } else {
            // Keep composer quiet
            if (stripos($command, 'composer') === 0) {
                $command = "$command --quiet";
            }

            $proc = proc_open($command, [
                0 => ['pipe', 'r'],
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ], $pipes);

            $cmdErr = stream_get_contents($pipes[2]);

            array_map('fclose', $pipes);

            $code = is_resource($proc)
                ? proc_close($proc)
                : -1;
        }

        // Restore the previous working directory
        chdir($prevCwd);

        if ($code !== 0) {
            $error = "Command failed with exit code $code";

            if (isset($cmdErr) && $cmdErr) {
                $error .= "and STDERR output:\n$cmdErr";
            } else {
                $error .= ".";
            }

            throw new RuntimeException($error);
        }
    }
}
