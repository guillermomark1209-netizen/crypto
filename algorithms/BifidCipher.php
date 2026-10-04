<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/helpers.php';

final class BifidCipher
{
    public static function calculate(string $input, string $key, string $operation, string $period): array
    {
        if (!preg_match('/^[A-Za-z]{1,100}$/', $key)) {
            throw new InvalidArgumentException('Keyword must contain 1–100 letters A–Z only.');
        }
        if (trim($input) === '' || strlen($input) > 2000) {
            throw new InvalidArgumentException('Enter a message containing 1–2,000 bytes.');
        }
        if (preg_match('//u', $input) !== 1) {
            throw new InvalidArgumentException('Input must be valid UTF-8 text.');
        }
        $prepared = preg_replace('/[^A-Z]/', '', str_replace('J', 'I', strtoupper($input))) ?? '';
        if ($prepared === '') {
            throw new InvalidArgumentException('Bifid input must contain at least one letter A–Z.');
        }
        if ($period !== '' && (!preg_match('/^[1-9][0-9]{0,3}$/', $period) || (int) $period > 2000)) {
            throw new InvalidArgumentException('Period must be a whole number from 1 to 2,000, or blank for the whole message.');
        }

        $blockSize = $period === '' ? strlen($prepared) : (int) $period;
        [$square, $positions] = alphabet_square($key);
        $decrypt = $operation === 'decrypt';
        $output = '';
        $blocks = [];

        foreach (str_split($prepared, $blockSize) as $blockIndex => $block) {
            $length = strlen($block);
            $coordinates = [];
            foreach (str_split($block) as $character) {
                [$row, $column] = $positions[$character];
                $coordinates[] = [$row, $column];
            }
            $rows = array_column($coordinates, 0);
            $columns = array_column($coordinates, 1);

            if ($decrypt) {
                $combined = [];
                foreach ($coordinates as [$row, $column]) {
                    $combined[] = $row;
                    $combined[] = $column;
                }
                $rowSequence = array_slice($combined, 0, $length);
                $columnSequence = array_slice($combined, $length);
                $regrouped = [];
                $blockOutput = '';
                for ($index = 0; $index < $length; $index++) {
                    $letter = square_cell($square, $rowSequence[$index], $columnSequence[$index]);
                    $regrouped[] = '(' . $rowSequence[$index] . ',' . $columnSequence[$index] . ') → ' . $letter;
                    $blockOutput .= $letter;
                }
                $stream = $combined;
                $shownRows = $rowSequence;
                $shownColumns = $columnSequence;
            } else {
                $stream = array_merge($rows, $columns);
                $regrouped = [];
                $blockOutput = '';
                for ($index = 0; $index < count($stream); $index += 2) {
                    $letter = square_cell($square, $stream[$index], $stream[$index + 1]);
                    $regrouped[] = '(' . $stream[$index] . ',' . $stream[$index + 1] . ') → ' . $letter;
                    $blockOutput .= $letter;
                }
                $shownRows = $rows;
                $shownColumns = $columns;
            }

            $coordinateText = [];
            foreach ($coordinates as $index => [$row, $column]) {
                $coordinateText[] = $block[$index] . " ({$row},{$column})";
            }
            $blocks[] = [
                'number' => $blockIndex + 1,
                'input' => $block,
                'coordinates' => implode(' · ', $coordinateText),
                'rows' => implode(' ', $shownRows),
                'columns' => implode(' ', $shownColumns),
                'combined' => implode(' ', $stream),
                'regrouped' => implode(' · ', $regrouped),
                'result' => $blockOutput,
            ];
            $output .= $blockOutput;
        }

        return [
            'output' => $output,
            'prepared' => $prepared,
            'key' => strtoupper($key),
            'period' => $blockSize,
            'square' => $square,
            'blocks' => $blocks,
        ];
    }
}
