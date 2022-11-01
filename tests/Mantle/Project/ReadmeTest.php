<?php

namespace RebelCode\Mantle\Tests\Project;

use bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use RebelCode\Mantle\Project;
use RebelCode\Mantle\Project\Config;
use RebelCode\Mantle\Project\Readme;
use RebelCode\Mantle\Project\Readme\Faq;

class ReadmeTest extends TestCase
{
    public function test_it_should_construct()
    {
        $excerpt = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.';
        $description = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec auctor, nisl eget aliquam.';
        $installation = 'Bacon ipsum dolor amet pork belly ham hock, bresaola short loin.';
        $faqs = [
            new Faq('What is Lorem Ipsum?', 'Lorem Ipsum is dummy text that is used as a placeholder.'),
        ];
        $screenshots = [
            '1. Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            '2. Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        ];

        $readme = new Readme($excerpt, $description, $installation, $faqs, $screenshots);

        $this->assertEquals($excerpt, $readme->excerpt);
        $this->assertEquals($description, $readme->description);
        $this->assertEquals($installation, $readme->installation);
        $this->assertEquals($faqs, $readme->faqs);
        $this->assertEquals($screenshots, $readme->screenshots);
    }

    public function provide_data_for_create_from_files_in_dir_test()
    {
        $excerpt = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.';
        $description = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec auctor, nisl eget aliquam.';
        $installation = 'Bacon ipsum dolor amet pork belly ham hock, bresaola short loin.';
        $faqs = [
            new Faq('What is Lorem Ipsum?', 'Lorem Ipsum is dummy text that is used as a placeholder.'),
            new Faq('What is the music of life?', 'Silence, my brother.'),
        ];
        $screenshots = [
            '1. Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            '2. Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        ];

        $files = [
            'excerpt.md' => $excerpt,
            'description.md' => $description,
            'installation.md' => $installation,
            'faqs' => [
                'what-is-lorem-ipsum.md' => $faqs[0]->question . "\n" . $faqs[0]->answer,
                'what-is-the-music-of-life.md' => $faqs[1]->question . "\n" . $faqs[1]->answer,
            ],
            'screenshots.md' => implode(PHP_EOL, $screenshots),
        ];

        $filesNoExcerpt = $files;
        unset($filesNoExcerpt['excerpt.md']);

        $filesNoDescription = $files;
        unset($filesNoDescription['description.md']);

        $filesNoInstallation = $files;
        unset($filesNoInstallation['installation.md']);

        $filesNoFaqs = $files;
        unset($filesNoFaqs['faqs']);

        $filesNoScreenshots = $files;
        unset($filesNoScreenshots['screenshots.md']);

        return [
            'all files' => [
                $files,
                new Readme($excerpt, $description, $installation, $faqs, $screenshots),
            ],
            'no excerpt' => [
                $filesNoExcerpt,
                new Readme('', $description, $installation, $faqs, $screenshots),
            ],
            'no description' => [
                $filesNoDescription,
                new Readme($excerpt, '', $installation, $faqs, $screenshots),
            ],
            'no installation' => [
                $filesNoInstallation,
                new Readme($excerpt, $description, '', $faqs, $screenshots),
            ],
            'no faqs' => [
                $filesNoFaqs,
                new Readme($excerpt, $description, $installation, [], $screenshots),
            ],
            'no screenshots' => [
                $filesNoScreenshots,
                new Readme($excerpt, $description, $installation, $faqs, []),
            ],
        ];
    }

    /** @dataProvider provide_data_for_create_from_files_in_dir_test */
    public function test_it_should_create_from_files_in_dir(array $files, Readme $expected)
    {
        $vfs = vfsStream::setup('root', null, $files);

        $readme = Readme::fromFilesInDir($vfs->url());

        $this->assertEquals($expected, $readme);
    }

    public function test_it_should_render()
    {
        $excerpt = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.';
        $description = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec auctor, nisl eget aliquam.';
        $installation = 'Bacon ipsum dolor amet pork belly ham hock, bresaola short loin.';
        $faqs = [
            new Faq('What is Lorem Ipsum?', 'Lorem Ipsum is dummy text that is used as a placeholder.'),
        ];
        $screenshots = [
            'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        ];

        $info = new Project\Info('Foo', '1.2.3', '');
        $info->version = '0.2';
        $info->minPhpVer = '7.1';
        $info->minWpVer = '5.8';
        $info->license = 'GPL-3.0';

        $info->wpOrg = new Project\WpOrgInfo('foo', 'Foo');
        $info->wpOrg->contributors = ['bar', 'baz'];
        $info->wpOrg->tags = ['lorem', 'ipsum'];
        $info->wpOrg->testedUpTo = '6.1';

        $vfs = vfsStream::setup('root', null, [
            'CHANGELOG.md' => implode("\n", [
                "# Change log",
                '',
                '## [0.2] - 2022-11-01',
                '### Added',
                '- (Pro) Added a feature',
                '',
                '### Fixed',
                '- Fixed a bug',
            ]),
        ]);

        $config = new Config();
        $config->publishBuild = 'Free';

        $project = $this->createConfiguredMock(Project::class, [
            'getInfo' => $info,
            'getChangelogPath' => $vfs->url() . '/CHANGELOG.md',
            'getConfig' => $config,
        ]);

        $readme = new Readme($excerpt, $description, $installation, $faqs, $screenshots);
        $result = $readme->render($project);

        $expected = implode("\n", [
            '=== Foo ===',
            '',
            "Contributors: bar, baz",
            "Tags: lorem, ipsum",
            "Stable tag: 0.2",
            "Tested up to: 6.1",
            "Requires at least: 5.8",
            "Requires PHP: 7.1",
            "License: GPL-3.0",
            '',
            $excerpt,
            '',
            '== Description ==',
            '',
            $description,
            '',
            '== Installation ==',
            '',
            $installation,
            '',
            '== FAQs ==',
            '',
            '= What is Lorem Ipsum? =',
            '',
            'Lorem Ipsum is dummy text that is used as a placeholder.',
            '',
            '== Screenshots ==',
            '',
            '1. Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            '2. Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            '',
            '== Changelog ==',
            '',
            '### 0.2 (2022-11-01)',
            '#### Fixed',
            '- Fixed a bug',
            '',
        ]);

        $this->assertEquals($expected, $result);
    }
}
