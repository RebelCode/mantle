<?php

namespace RebelCode\Mantle\Project;

use DirectoryIterator;
use RebelCode\Mantle\Project;
use RebelCode\Mantle\Project\Readme\Changelog;
use RebelCode\Mantle\Project\Readme\Faq;
use RuntimeException;
use SplFileInfo;

class Readme
{
    /** @var string */
    public $excerpt = '';
    /** @var string */
    public $description = '';
    /** @var string */
    public $installation = '';
    /** @var Faq[] */
    public $faqs = [];
    /** @var string[] */
    public $screenshots = [];

    /**
     * Constructor.
     *
     * @param string $excerpt The short description of the plugin.
     * @param string $description The long description of the plugin.
     * @param string $installation The installation instructions for the plugin.
     * @param Faq[] $faqs The FAQs for the plugin.
     * @param string[] $screenshots The screenshot captions for the plugin.
     */
    public function __construct(
        string $excerpt = '',
        string $description = '',
        string $installation = '',
        array $faqs = [],
        array $screenshots = []
    ) {
        $this->excerpt = $excerpt;
        $this->description = $description;
        $this->installation = $installation;
        $this->faqs = $faqs;
        $this->screenshots = $screenshots;
    }

    /** Creates an instance from the files in a specific directory. */
    public static function fromFilesInDir(string $path): self
    {
        $excerpt = is_readable($path . '/excerpt.md')
            ? trim(file_get_contents($path . '/excerpt.md'))
            : '';

        $description = is_readable($path . '/description.md')
            ? trim(file_get_contents($path . '/description.md'))
            : '';

        $installation = is_readable($path . '/installation.md')
            ? trim(file_get_contents($path . '/installation.md'))
            : '';

        $faqs = [];
        foreach (new DirectoryIterator($path . '/faqs') as $faqFile) {
            if ($faqFile instanceof SplFileInfo && $faqFile->isFile()) {
                $faqs[] = Faq::fromFile($faqFile->getPathname());
            }
        }

        $screenshots = file($path . '/screenshots.md', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $screenshots + array_map('trim', $screenshots);

        return new self(
            $excerpt,
            $description,
            $installation,
            $faqs,
            $screenshots
        );
    }

    /** Renders the readme. */
    public function render(Project $project): string
    {
        $wpOrgInfo = $project->getInfo()->wpOrg;

        if ($wpOrgInfo === null) {
            throw new RuntimeException('The project has no WordPress.org plugin information.');
        }

        $info = $project->getInfo();
        $contributors = implode(', ', $wpOrgInfo->contributors);
        $tags = implode(', ', $wpOrgInfo->tags);

        $string = <<<README
        === {$wpOrgInfo->name} ===
        
        Contributors: {$contributors}
        Tags: {$tags}
        Stable tag: {$info->version}
        Tested up to: {$wpOrgInfo->testedUpTo}
        Requires at least: {$info->minWpVer}
        Requires PHP: {$info->minPhpVer}
        License: {$info->license}

        {$this->excerpt}
        
        == Description ==
        
        {$this->description}


        README;

        if ($this->installation) {
            $string .= <<<README
            == Installation ==
            
            {$this->installation}
            

            README;
        }

        if (count($this->faqs) > 0) {
            $string .= "== FAQs ==\n\n";

            $faqStrings = [];

            foreach ($this->faqs as $faq) {
                $faqStrings[] = "= {$faq->question} =\n\n{$faq->answer}";
            }

            $string .= implode("\n\n- - -\n\n", $faqStrings) . "\n\n";
        }

        if (count($this->screenshots) > 0) {
            $string .= "== Screenshots ==\n\n";

            foreach ($this->screenshots as $idx => $caption) {
                $string .= "$idx. $caption\n";
            }

            $string .= "\n";
        }

        $changelogPath = $project->getChangelogPath();
        if ($changelogPath !== null) {
            $changelogMd = file_get_contents($changelogPath);
            $changelog = Changelog::parseMarkdown($changelogMd);

            $string .= "== Changelog ==\n\n";
            $string .= $changelog->toWpOrgFormat(['', $project->getConfig()->publishBuild]);
            $string .= "\n";
        }

        return $string;
    }
}
