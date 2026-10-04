<?php
declare(strict_types=1);

final class CaesarCipher
{
    public static function calculate(string $input, string $key, string $operation): array
    {
        if (!preg_match('/^(?:[0-9]|1[0-9]|2[0-5])$/', $key)) {
            throw new InvalidArgumentException('Shift must be an integer from 0 to 25.');
        }
        self::validateInput($input);
        $shift = (int) $key;
        $decrypt = $operation === 'decrypt';
        $output = '';
        $steps = [];
        $characters = preg_split('//u', $input, -1, PREG_SPLIT_NO_EMPTY);
        if ($characters === false) {
            throw new InvalidArgumentException('Input must be valid UTF-8 text.');
        }
        foreach ($characters as $character) {
            if (preg_match('/^[A-Za-z]$/', $character)) {
                $value = ord(strtoupper($character)) - 65;
                $resultValue = ($value + ($decrypt ? 26 - $shift : $shift)) % 26;
                $result = chr(65 + $resultValue);
                if (ctype_lower($character)) {
                    $result = strtolower($result);
                }
                $output .= $result;
                $calculation = $decrypt
                    ? "({$value} - {$shift} + 26) mod 26 = {$resultValue}"
                    : "({$value} + {$shift}) mod 26 = {$resultValue}";
                $steps[] = [$character, (string) $value, $calculation, $result];
            } else {
                $output .= $character;
                $steps[] = [$character, '—', 'Non-letter preserved; key does not apply', $character];
            }
        }

        return ['output' => $output, 'prepared' => $input, 'key' => $shift, 'steps' => $steps];
    }

    private static function validateInput(string $input): void
    {
        if (trim($input) === '' || strlen($input) > 2000) {
            throw new InvalidArgumentException('Enter a message containing 1–2,000 bytes.');
        }
        if (preg_match('//u', $input) !== 1) {
            throw new InvalidArgumentException('Input must be valid UTF-8 text.');
        }
    }
}
