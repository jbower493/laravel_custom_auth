<?php

namespace App\RecipeUtils;

use Illuminate\Support\Facades\Http;

// Docs for simple html dom file
// https://simplehtmldom.sourceforge.io/docs/1.9/api/file_get_html/#

class FromUrl
{
    protected $url;

    public function __construct($url)
    {
        $this->url = $url;
    }

    private function fetchPage()
    {
        $response = Http::get($this->url);

        return $response;
    }

    private function getHtmlBodyNoNewLines($htmlResponse)
    {
        $body = $htmlResponse->body();
        $noNewLines = str_replace("\n", '', $body);

        return $noNewLines;
    }

    public function getInstructions()
    {
        $page = $this->fetchPage();
        $bodyNoNewLines = $this->getHtmlBodyNoNewLines($page);

        $methodRegex = '/Method<\/h2>.*<\/ol>/';
        preg_match($methodRegex, $bodyNoNewLines, $methodMatches);
        $methodChunk = $methodMatches[0];

        // Find the "steps" (li's) within that section
        $document = str_get_html($methodChunk);

        $listElements = $document->find('li');

        $steps = [];

        foreach ($listElements as $index => $listElement) {
            $child = $listElement->first_child();
            $text = $child->innertext();
            $steps[$index] = strval($index + 1) . ') ' . $text;
        }

        $newInstructions = implode("\n", $steps);

        return $newInstructions;
    }

    public function getIngredients()
    {
        // TODO
    }
}
