<?php

namespace DevTest;

use mysqli;

class Database implements DatabaseInterface
{
    private mysqli $mysqli;

    public function __construct(mysqli $mysqli)
    {
        $this->mysqli = $mysqli;
    }

    public function buildQuery(string $query, array $args = []): string
    {
        $blockProcessor = new ConditionalBlockProcessor();
        $query = $blockProcessor->processConditionalBlocks($query, $args);

        $placeholderReplacer = new PlaceholderReplacementServices($this->mysqli);
        return $placeholderReplacer->replacePlaceholders($query, $args);
    }

    public function skip()
    {
        return ConditionalBlockProcessor::SKIP_BLOCK;
    }
}