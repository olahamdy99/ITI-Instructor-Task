<?php

namespace App\Services;

use InvalidArgumentException;

class DocumentChunker
{
    public function chunk(
        string $text,
        int $chunkSize = 1000,
        int $overlap = 200
    ): array {
        if ($chunkSize <= 0) {
            throw new InvalidArgumentException(
                'Chunk size must be greater than zero.'
            );
        }

        if ($overlap < 0 || $overlap >= $chunkSize) {
            throw new InvalidArgumentException(
                'Overlap must be greater than or equal to zero and smaller than chunk size.'
            );
        }

        $chunks = [];

        $length = mb_strlen($text);
        $start = 0;
        $index = 0;

        while ($start < $length) {
            $end = min($start + $chunkSize, $length);

            if ($end < $length) {
                $candidate = mb_substr(
                    $text,
                    $start,
                    $chunkSize
                );

                $lastNewLine = mb_strrpos(
                    $candidate,
                    "\n"
                );

                $lastSpace = mb_strrpos(
                    $candidate,
                    ' '
                );

                $breakPositions = array_filter(
                    [$lastNewLine, $lastSpace],
                    fn ($position) => $position !== false
                );

                if (!empty($breakPositions)) {
                    $safeBreak = max($breakPositions);

                    if ($safeBreak > 0) {
                        $end = $start + $safeBreak;
                    }
                }
            }

            $content = trim(
                mb_substr(
                    $text,
                    $start,
                    $end - $start
                )
            );

            if ($content !== '') {
                $chunks[] = [
                    'chunk_index' => $index,
                    'content' => $content,
                ];

                $index++;
            }

            if ($end >= $length) {
                break;
            }

            $nextStart = max(
                0,
                $end - $overlap
            );

            if ($nextStart > 0) {
                $remaining = mb_substr(
                    $text,
                    $nextStart,
                    $overlap
                );

                $firstSpace = mb_strpos(
                    $remaining,
                    ' '
                );

                $firstNewLine = mb_strpos(
                    $remaining,
                    "\n"
                );

                $safeStart = null;

                if (
                    $firstSpace !== false &&
                    $firstNewLine !== false
                ) {
                    $safeStart = min(
                        $firstSpace,
                        $firstNewLine
                    );
                } elseif ($firstSpace !== false) {
                    $safeStart = $firstSpace;
                } elseif ($firstNewLine !== false) {
                    $safeStart = $firstNewLine;
                }

                if ($safeStart !== null) {
                    $nextStart += $safeStart + 1;
                }
            }

            $start = $nextStart;
        }

        return $chunks;
    }
}