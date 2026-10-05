<?php

namespace App\Services;

use Mews\Purifier\Facades\Purifier;

class HtmlSanitizer
{
    public function clean(?string $html): ?string
    {
        if ($html === null || trim(strip_tags($html, '<img>')) === '') {
            return null;
        }

        return Purifier::clean($html, 'jodit');
    }
}
