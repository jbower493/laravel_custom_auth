<?php

namespace App\RecipeUtils;

use Exception;
use Illuminate\Support\Facades\Http;

// Docs for simple html dom file
// https://simplehtmldom.sourceforge.io/docs/1.9/api/file_get_html/#

// BBC examples
// 1. https://www.bbc.co.uk/food/recipes/sausage_and_gnocchi_bake_80924
// 2. https://www.bbc.co.uk/food/recipes/miso_prawn_and_mushroom_81596
// 3. https://www.bbc.co.uk/food/recipes/chilliconcarne_67875

// Delish examples
// 1. https://www.delish.com/cooking/recipe-ideas/a28626172/how-to-cook-boneless-chicken-thigh-oven-recipe/
// 2. https://www.delish.com/cooking/recipe-ideas/recipes/a54961/chicken-caesar-wraps-recipe/

// Issues:
// - With Delish 2. example, last bullet point has a <br /> tag at the end of the text

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

    private function determineExtractInstructionsSuccess($newInstructions)
    {
        // TODO: implement

        return true;
    }

    private function extractInstructionsFromBodyBbc($htmlBodyNoNewLines)
    {
        try {
            $methodRegex = '/Method<\/h2>.*<\/ol>/';
            preg_match($methodRegex, $htmlBodyNoNewLines, $methodMatches);
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

            $instructions = implode("\n", $steps);

            $isSuccess = $this->determineExtractInstructionsSuccess($instructions);

            if (!$isSuccess) {
                throw new Exception('Instructions were not extracted.');
            }

            return [
                "success" => true,
                "instructions" => $instructions
            ];
        } catch (Exception) {
            return [
                "success" => false,
                "instructions" => ''
            ];
        }
    }

    private function extractInstructionsFromBodyDelish($htmlBodyNoNewLines)
    {
        try {
            $methodRegex = '/Directions<\/h2>.*<\/ol>/';
            preg_match($methodRegex, $htmlBodyNoNewLines, $methodMatches);
            $methodChunk = $methodMatches[0];

            // Find the "steps" (li's) within that section
            $document = str_get_html($methodChunk);

            $ol = $document->find('ol');
            $listElements = $ol[0]->find('li');

            $steps = [];

            foreach ($listElements as $index => $listElement) {
                $liContent = $listElement->innertext();

                $textArr = explode('</span>', $liContent);
                $text = $textArr[count($textArr) - 1];

                $steps[$index] = strval($index + 1) . ') ' . $text;
            }

            $instructions = implode("\n", $steps);

            $isSuccess = $this->determineExtractInstructionsSuccess($instructions);

            if (!$isSuccess) {
                throw new Exception('Instructions were not extracted.');
            }

            return [
                "success" => true,
                "instructions" => $instructions
            ];
        } catch (Exception) {
            return [
                "success" => false,
                "instructions" => ''
            ];
        }
    }

    public function getInstructions()
    {
        // Get the HTML body
        $page = $this->fetchPage();
        $bodyNoNewLines = $this->getHtmlBodyNoNewLines($page);

        // Pull out the instructions, attempt different methods
        $attemptInstructions = $this->extractInstructionsFromBodyBbc($bodyNoNewLines);

        if (!$attemptInstructions['success']) {
            $attemptInstructions = $this->extractInstructionsFromBodyDelish($bodyNoNewLines);
        }

        return $attemptInstructions;
    }

    public function getIngredients()
    {
        // Get the HTML body
        $page = $this->fetchPage();
        $bodyNoNewLines = $this->getHtmlBodyNoNewLines($page);

        // Pull out the ingredients
        // TODO

        return '';
    }
}
