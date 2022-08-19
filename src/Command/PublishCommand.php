<?php

namespace RebelCode\Mantle\Command;

use RebelCode\Mantle\MantleOutputStyle;
use RebelCode\Mantle\Project;
use Symfony\Component\Console\Input\InputInterface;

class PublishCommand extends BaseCommand
{
    protected function setup(): void
    {
        $this->setName('publish');
    }

    protected function runCommand(Project $project, MantleOutputStyle $io, InputInterface $input): int
    {
        $project->publish();

        $io->success('Published to WordPress.org');
        $io->writeln("<fg=green>Check it out: {$project->getInfo()->wpOrg->getUrl()}</>");

        return 0;
    }
}
