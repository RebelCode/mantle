<?php

namespace RebelCode\Mantle\Tests\Project\Readme;

use PHPUnit\Framework\TestCase;
use RebelCode\Mantle\Project;
use RebelCode\Mantle\Project\Readme\Changelog;
use RebelCode\Mantle\Project\Readme\Changelog\ChangelogCategory;
use RebelCode\Mantle\Project\Readme\Changelog\ChangelogEntry;
use RebelCode\Mantle\Project\Readme\Changelog\ChangelogVersion;

class ChangelogTest extends TestCase
{
    public function test_it_should_set_properties()
    {
        $versions = [
            $this->createMock(ChangelogVersion::class),
            $this->createMock(ChangelogVersion::class),
            $this->createMock(ChangelogVersion::class),
        ];

        $changelog = new Changelog($versions);

        $this->assertSame($versions, $changelog->versions);
    }

    public function test_it_should_parse_markdown()
    {
        $project = new Project('', new Project\Config(), new Project\Info('test', '1.0', 'main.php'));
        $project->addBuild(new Project\Build('foo', $project));
        $project->addBuild(new Project\Build('bar', $project));

        $markdown = <<<MARKDOWN
        # Change log
        All notable changes to this project will be documented in this file.
        
        The format is based on [Keep a Changelog](http://keepachangelog.com/)
        and this project adheres to [Semantic Versioning](http://semver.org/).

        ## [Unreleased]
        ### Added
        - (Else) Added a feature

        ## [1.0.0] - 2022-08-12
        ### Added
        - (Foo) Added a feature
        
        ### Fixed
        - (Bar) Fixed a bug
        
        ## [0.1] - 2022-03-15
        Initial release
        MARKDOWN;

        $changelog = Changelog::parseMarkdown($markdown, $project);

        // Check versions
        $this->assertCount(3, $changelog->versions);
        $this->assertSame('Unreleased', $changelog->versions[0]->number);
        $this->assertSame('1.0.0', $changelog->versions[1]->number);
        $this->assertSame('0.1', $changelog->versions[2]->number);

        // Check Unreleased > Added
        $this->assertCount(1, $changelog->versions[0]->categories[0]->entries);
        $this->assertNull($changelog->versions[0]->categories[0]->entries[0]->tag);
        $this->assertSame('(Else) Added a feature', $changelog->versions[0]->categories[0]->entries[0]->message);

        // Check 1.0.0 > Added
        $this->assertCount(1, $changelog->versions[1]->categories[0]->entries);
        $this->assertSame('foo', $changelog->versions[1]->categories[0]->entries[0]->tag);
        $this->assertSame('Added a feature', $changelog->versions[1]->categories[0]->entries[0]->message);

        // Check 1.0.0 > Fixed
        $this->assertCount(1, $changelog->versions[1]->categories[1]->entries);
        $this->assertSame('bar', $changelog->versions[1]->categories[1]->entries[0]->tag);
        $this->assertSame('Fixed a bug', $changelog->versions[1]->categories[1]->entries[0]->message);

        // Check 0.1 message
        $this->assertSame('Initial release', $changelog->versions[2]->message);
    }

    public function test_it_should_render_as_wporg_format()
    {
        $changelog = new Changelog([
            new ChangelogVersion('0.3', '2022-03-03', '', [
                new ChangelogCategory(ChangelogCategory::ADDED, [
                    new ChangelogEntry('Added a feature'),
                ]),
                new ChangelogCategory(ChangelogCategory::CHANGED, [
                    new ChangelogEntry('Fixed typos'),
                    new ChangelogEntry('Improved UX', 'pro'),
                ]),
            ]),
            new ChangelogVersion('0.2', '2022-02-02', '', [
                new ChangelogCategory(ChangelogCategory::ADDED, [
                    new ChangelogEntry('Added a feature'),
                    new ChangelogEntry('Added another feature'),
                ]),
                new ChangelogCategory(ChangelogCategory::FIXED, [
                    new ChangelogEntry('Fixed a bug', 'pro'),
                ]),
            ]),
            new ChangelogVersion('0.1', '2022-01-01', 'Initial release'),
        ]);

        $expected = <<<CHANGELOG
        ### 0.3 (2022-03-03)
        #### Added
        - Added a feature
        
        #### Changed
        - Fixed typos
        - (pro) Improved UX
        
        ### 0.2 (2022-02-02)
        #### Added
        - Added a feature
        - Added another feature
        
        #### Fixed
        - (pro) Fixed a bug
        
        ### 0.1 (2022-01-01)
        Initial release
        CHANGELOG;

        $this->assertEquals($expected, $changelog->toWpOrgFormat());
    }

    public function test_it_should_render_as_wporg_format_with_empty_tag()
    {
        $changelog = new Changelog([
            new ChangelogVersion('0.3', '2022-03-03', '', [
                new ChangelogCategory(ChangelogCategory::ADDED, [
                    new ChangelogEntry('Added a feature'),
                ]),
                new ChangelogCategory(ChangelogCategory::CHANGED, [
                    new ChangelogEntry('Fixed typos'),
                    new ChangelogEntry('Improved UX', 'pro'),
                ]),
            ]),
            new ChangelogVersion('0.2', '2022-02-02', '', [
                new ChangelogCategory(ChangelogCategory::ADDED, [
                    new ChangelogEntry('Added a feature'),
                    new ChangelogEntry('Added another feature'),
                ]),
                new ChangelogCategory(ChangelogCategory::FIXED, [
                    new ChangelogEntry('Fixed a bug', 'pro'),
                ]),
            ]),
            new ChangelogVersion('0.1', '2022-01-01', 'Initial release'),
        ]);

        $expected = <<<CHANGELOG
        ### 0.3 (2022-03-03)
        #### Added
        - Added a feature
        
        #### Changed
        - Fixed typos
        
        ### 0.2 (2022-02-02)
        #### Added
        - Added a feature
        - Added another feature
        
        ### 0.1 (2022-01-01)
        Initial release
        CHANGELOG;

        $this->assertEquals($expected, $changelog->toWpOrgFormat(['']));
    }

    public function test_it_should_render_as_wporg_format_with_tag()
    {
        $changelog = new Changelog([
            new ChangelogVersion('0.3', '2022-03-03', '', [
                new ChangelogCategory(ChangelogCategory::ADDED, [
                    new ChangelogEntry('Added a feature'),
                ]),
                new ChangelogCategory(ChangelogCategory::CHANGED, [
                    new ChangelogEntry('Fixed typos'),
                    new ChangelogEntry('Improved UX', 'pro'),
                ]),
            ]),
            new ChangelogVersion('0.2', '2022-02-02', '', [
                new ChangelogCategory(ChangelogCategory::ADDED, [
                    new ChangelogEntry('Added a feature'),
                    new ChangelogEntry('Added another feature'),
                ]),
                new ChangelogCategory(ChangelogCategory::FIXED, [
                    new ChangelogEntry('Fixed a bug', 'pro'),
                ]),
            ]),
            new ChangelogVersion('0.1', '2022-01-01', 'Initial release'),
        ]);

        $expected = <<<CHANGELOG
        ### 0.3 (2022-03-03)
        #### Changed
        - (pro) Improved UX
        
        ### 0.2 (2022-02-02)
        #### Fixed
        - (pro) Fixed a bug
        
        ### 0.1 (2022-01-01)
        Initial release
        CHANGELOG;

        $this->assertEquals($expected, $changelog->toWpOrgFormat(['pro']));
    }

    public function test_it_should_render_as_wporg_format_without_tags()
    {
        $changelog = new Changelog([
            new ChangelogVersion('0.3', '2022-03-03', '', [
                new ChangelogCategory(ChangelogCategory::ADDED, [
                    new ChangelogEntry('Added a feature'),
                ]),
                new ChangelogCategory(ChangelogCategory::CHANGED, [
                    new ChangelogEntry('Fixed typos'),
                    new ChangelogEntry('Improved UX', 'pro'),
                ]),
            ]),
            new ChangelogVersion('0.2', '2022-02-02', '', [
                new ChangelogCategory(ChangelogCategory::ADDED, [
                    new ChangelogEntry('Added a feature'),
                    new ChangelogEntry('Added another feature'),
                ]),
                new ChangelogCategory(ChangelogCategory::FIXED, [
                    new ChangelogEntry('Fixed a bug', 'pro'),
                ]),
            ]),
            new ChangelogVersion('0.1', '2022-01-01', 'Initial release'),
        ]);

        $expected = <<<CHANGELOG
        ### 0.3 (2022-03-03)
        #### Changed
        - Improved UX
        
        ### 0.2 (2022-02-02)
        #### Fixed
        - Fixed a bug
        
        ### 0.1 (2022-01-01)
        Initial release
        CHANGELOG;

        $this->assertEquals($expected, $changelog->toWpOrgFormat(['pro'], false));
    }

    public function test_it_should_skip_empty_versions()
    {
        $changelog = new Changelog([
            new ChangelogVersion('0.3', '2022-03-03', '', [
                new ChangelogCategory(ChangelogCategory::ADDED, [
                    new ChangelogEntry('Added a feature'),
                ]),
                new ChangelogCategory(ChangelogCategory::CHANGED, [
                    new ChangelogEntry('Fixed typos'),
                    new ChangelogEntry('Improved UX', 'pro'),
                ]),
            ]),
            new ChangelogVersion('0.2', '2022-02-02', '', [
                new ChangelogCategory(ChangelogCategory::ADDED, []),
                new ChangelogCategory(ChangelogCategory::FIXED, []),
            ]),
            new ChangelogVersion('0.1', '2022-01-01', 'Initial release'),
        ]);

        $expected = <<<CHANGELOG
        ### 0.3 (2022-03-03)
        #### Changed
        - Improved UX
        
        ### 0.2 (2022-02-02)
        
        ### 0.1 (2022-01-01)
        Initial release
        CHANGELOG;

        $this->assertEquals($expected, $changelog->toWpOrgFormat(['pro'], false));
    }
}
