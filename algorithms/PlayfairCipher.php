<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/helpers.php';

final class PlayfairCipher
{
    public static function calculate(string $input, string $key, string $operation): array
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
            throw new InvalidArgumentException('Playfair input must contain at least one letter A–Z.');
        }

        [$square, $positions] = alphabet_square($key);
        $decrypt = $operation === 'decrypt';
        if ($decrypt) {
            if (strlen($prepared) % 2 !== 0) {
                throw new InvalidArgumentException('Playfair ciphertext must contain an even number of letters after normalization.');
            }
        } else {
            $source = $prepared;
            $prepared = '';
            for ($index = 0, $length = strlen($source); $index < $length;) {
                $first = $source[$index++];
                $second = $source[$index] ?? null;
                if ($second === null || $second === $first) {
                    $second = $first === 'X' ? 'Q' : 'X';
                } else {
                    $index++;
                }
                $prepared .= $first . $second;
            }
        }

        $output = '';
        $steps = [];
        for ($index = 0; $index < strlen($prepared); $index += 2) {
            $first = $prepared[$index];
            $second = $prepared[$index + 1];
            [$row1, $column1] = $positions[$first];
            [$row2, $column2] = $positions[$second];
            if ($row1 === $row2) {
                $direction = $decrypt ? -1 : 1;
                $result = square_cell($square, $row1, (($column1 - 1 + $direction + 5) % 5) + 1)
                    . square_cell($square, $row2, (($column2 - 1 + $direction + 5) % 5) + 1);
                $rule = $decrypt ? 'Same row → move left' : 'Same row → move right';
            } elseif ($column1 === $column2) {
                $direction = $decrypt ? -1 : 1;
                $result = square_cell($square, (($row1 - 1 + $direction + 5) % 5) + 1, $column1)
                    . square_cell($square, (($row2 - 1 + $direction + 5) % 5) + 1, $column2);
                $rule = $decrypt ? 'Same column → move up' : 'Same column → move down';
            } else {
                $result = square_cell($square, $row1, $column2) . square_cell($square, $row2, $column1);
                $rule = 'Rectangle → swap columns';
            }
            $output .= $result;
            $steps[] = [
                $first . $second,
                "{$first} ({$row1},{$column1}); {$second} ({$row2},{$column2})",
                $rule,
                $result,
            ];
        }

        return ['output' => $output, 'prepared' => $prepared, 'key' => strtoupper($key), 'square' => $square, 'steps' => $steps];
    }
}
