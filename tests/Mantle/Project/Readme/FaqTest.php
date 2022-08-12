<?php

namespace RebelCode\Mantle\Tests\Project\Readme;

use bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use RebelCode\Mantle\Project\Readme\Faq;
use RuntimeException;

class FaqTest extends TestCase
{
    public function test_it_should_set_properties()
    {
        $faq = new Faq('Question', 'Answer');

        $this->assertEquals('Question', $faq->question);
        $this->assertEquals('Answer', $faq->answer);
    }

    public function test_it_should_create_from_file()
    {
        $question = 'What is the meaning of life?';
        $answer = "It's 42, obviously.";

        $vfs = vfsStream::setup('root', null, [
            'faq.txt' => "$question\n$answer",
        ]);

        $faq = Faq::fromFile($vfs->url() . '/faq.txt');

        $this->assertEquals($question, $faq->question);
        $this->assertEquals($answer, $faq->answer);
    }

    public function test_it_should_create_from_file_with_empty_line_separator()
    {
        $question = 'What is the meaning of life?';
        $answer = "It's 42, obviously.";

        $vfs = vfsStream::setup('root', null, [
            'faq.txt' => "$question\n\n$answer",
        ]);

        $faq = Faq::fromFile($vfs->url() . '/faq.txt');

        $this->assertEquals($question, $faq->question);
        $this->assertEquals($answer, $faq->answer);
    }

    public function test_it_should_throw_if_not_enough_lines()
    {
        $this->expectException(RuntimeException::class);

        $vfs = vfsStream::setup('root', null, [
            'faq.txt' => "What is the meaning of life? It's 42, obviously.",
        ]);

        Faq::fromFile($vfs->url() . '/faq.txt');
    }
}
