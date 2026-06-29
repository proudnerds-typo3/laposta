<?php

declare(strict_types=1);

namespace Proudnerds\Laposta\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Parses a flash message body into render blocks: paragraphs and a bulleted list.
 *
 * The controller joins lines with a newline and prefixes newsletter names with
 * LIST_ITEM_PREFIX. Consecutive prefixed lines become one list block; other lines become
 * paragraphs. A list with a single item is rendered as a paragraph (no list of one), so a
 * single newsletter does not get a stray bullet. No <br> and no HTML are built in PHP.
 *
 * Usage: {flashMessage.message -> laposta:messageBlocks()}
 */
final class MessageBlocksViewHelper extends AbstractViewHelper
{
    public const LIST_ITEM_PREFIX = '• ';

    /**
     * The block text is escaped where it is output (e.g. <p>{block.text}</p>), so the raw
     * value must reach this view helper unescaped.
     */
    protected $escapeChildren = false;

    /**
     * Output is an array of blocks, not a string, so output escaping does not apply here.
     */
    protected $escapeOutput = false;

    public function initializeArguments(): void
    {
        $this->registerArgument('value', 'string', 'The flash message body; defaults to the tag content.');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function render(): array
    {
        $value = (string)($this->arguments['value'] ?? $this->renderChildren());
        $lines = preg_split('/\r\n|\r|\n/', $value) ?: [];

        $blocks = [];
        $items = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            if (str_starts_with($line, self::LIST_ITEM_PREFIX)) {
                $items[] = trim(substr($line, strlen(self::LIST_ITEM_PREFIX)));
                continue;
            }
            $blocks = $this->flushItems($blocks, $items);
            $items = [];
            $blocks[] = ['type' => 'paragraph', 'text' => $line];
        }

        return $this->flushItems($blocks, $items);
    }

    /**
     * Append the collected list items to the blocks: a single item becomes a paragraph,
     * two or more become a list.
     *
     * @param array<int, array<string, mixed>> $blocks
     * @param array<int, string> $items
     * @return array<int, array<string, mixed>>
     */
    private function flushItems(array $blocks, array $items): array
    {
        if (count($items) === 1) {
            $blocks[] = ['type' => 'paragraph', 'text' => $items[0]];
        } elseif ($items !== []) {
            $blocks[] = ['type' => 'list', 'items' => $items];
        }

        return $blocks;
    }
}
