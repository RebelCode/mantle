<?php

namespace RebelCode\Mantle\Project\Readme;

use RuntimeException;

class Faq
{
    /** @var string */
    public $question;
    /** @var string */
    public $answer;

    /**
     * Constructor.
     *
     * @param string $question The question.
     * @param string $answer The answer.
     */
    public function __construct(string $question, string $answer)
    {
        $this->question = $question;
        $this->answer = $answer;
    }

    /** Creates an FAQ from a file. */
    public static function fromFile(string $filePath): self
    {
        $lines = file($filePath, FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES);

        if (count($lines) < 2) {
            throw new RuntimeException(
                sprintf(
                    'The FAQ file "%s" is invalid: it must contain a question line and at least 1 answer line.',
                    $filePath
                )
            );
        }

        $lines = array_map('trim', $lines);
        $question = $lines[0];
        $answer = implode("\n", array_slice($lines, 1));

        return new self($question, $answer);
    }
}
