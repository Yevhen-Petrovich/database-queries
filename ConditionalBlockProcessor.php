<?php

namespace DevTest;

class ConditionalBlockProcessor
{
    public const SKIP_BLOCK = '__SKIP__';

    public function processConditionalBlocks(string $query, array &$args): string
    {
        $pattern = '/\{([^{}]*)\}/';
        return preg_replace_callback($pattern, function ($matches) use (&$args) {
            $blockContent = $matches[1];
            $shouldSkip = false;

            foreach ($args as $arg) {
                if ($arg === self::SKIP_BLOCK) {
                    $shouldSkip = true;
                    break;
                }
            }

            return $shouldSkip ? '' : $blockContent;
        }, $query);
    }
}