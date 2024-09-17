<?php

namespace App\Services;

class SpamDetector
{
    protected $spamKeywords = ['buy now', 'free', 'winner', 'click here'];

    public function isSpam($text)
    {
        foreach ($this->spamKeywords as $keyword) {
            if (stripos($text, $keyword) !== false) {
                return true;
            }
        }

        return false;
    }
}
