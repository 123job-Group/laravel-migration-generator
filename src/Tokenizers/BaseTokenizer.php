<?php

namespace LaravelMigrationGenerator\Tokenizers;

use LaravelMigrationGenerator\Helpers\WritableTrait;

abstract class BaseTokenizer
{
    use WritableTrait;

    protected $tokens = [];

    private string $value;

    private const SPACE_REPLACER = '&!@';

    public function __construct(string $value)
    {
        $this->value = $value;
        $prune = false;
        //\(?\'(.+?)?\s(.+?)?\'\)?
        if (preg_match_all("/'([A-Za-z ]+)'(?=[^A-Za-z])/", $value, $matches)) {
            foreach ($matches[0] as $quoteWithSpace) {
                //we've got an enum or set that has spaces in the text
                //so we'll convert to a different character so it doesn't get pruned
                $toReplace = $quoteWithSpace;
                $value = str_replace($toReplace, str_replace(' ', self::SPACE_REPLACER, $toReplace), $value);
                $prune = true;
            }
        }
        $this->tokens = array_map(function ($item) {
            return trim($item, ', ');
        }, str_getcsv($value, ' ', "'"));

        if ($prune) {
            $this->tokens = array_map(function ($item) {
                return str_replace(self::SPACE_REPLACER, ' ', $item);
            }, $this->tokens);
        }
    }

    public static function make(string $line)
    {
        return new static($line);
    }

    /**
     * @param string $line
     * @return static
     */
    public static function parse(string $line)
    {
        return (new static($line))->tokenize();
    }

    protected function parseColumn($value)
    {
        return trim($value, '` ');
    }

    protected function columnsToArray($string)
    {
        $string = trim($string, '()');

        return array_map(fn ($item) => $this->parseColumn($item), explode(',', $string));
    }

    protected function consume()
    {
        return array_shift($this->tokens);
    }

    protected function putBack($value)
    {
        array_unshift($this->tokens, $value);
    }
}
