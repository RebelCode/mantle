<?php

namespace RebelCode\Mantle;

use Symfony\Component\Console\Style\SymfonyStyle;

class MantleOutputStyle extends SymfonyStyle
{
    public function topLevel(string $message): void
    {
        $this->writeln("=> $message");
    }

    public function writeBuild(string $name): void
    {
        $this->topLevel("Building <fg=cyan>$name</>");
    }

    public function writeStep(string $name): void
    {
        $message = "   - {$name}";

        if ($this->getVerbosity() > self::VERBOSITY_NORMAL) {
            $this->writeln($message);
        } else {
            $this->write($message . ' ');
        }
    }

    public function endStep()
    {
        if ($this->getVerbosity() <= self::VERBOSITY_NORMAL) {
            $this->writeln(' <fg=green>✓</fg=green>');
        }
    }

    public function writeInstruction(string $color, string $format, ...$args): void
    {
        if ($this->getVerbosity() > self::VERBOSITY_NORMAL) {
            $args = array_map(function ($arg) {
                return "<fg=default>$arg</>";
            }, $args);

            $this->writeln("<fg=$color>     * " . vsprintf($format, $args) . '</>');
        } else {
            $this->write('<fg=gray>.</>');
        }
    }
}
