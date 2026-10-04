<?php
declare(strict_types=1);

final class VigenereCipher
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

        $key = strtoupper($key);
        $decrypt = $operation === 'decrypt';
        $keyIndex = 0;
        $output = '';
        $repeatedKey = '';
        $steps = [];
        $characters = preg_split('//u', $input, -1, PREG_SPLIT_NO_EMPTY);
        if ($characters === false) {
            throw new InvalidArgumentException('Input must be valid UTF-8 text.');
        }
        foreach ($characters as $character) {
            if (preg_match('/^[A-Za-z]$/', $character)) {
                $keyCharacter = $key[$keyIndex++ % strlen($key)];
                $inputValue = ord(strtoupper($character)) - 65;
                $keyValue = ord($keyCharacter) - 65;
                $resultValue = ($inputValue + ($decrypt ? 26 - $keyValue : $keyValue)) % 26;
                $result = chr(65 + $resultValue);
                if (ctype_lower($character)) {
                    $result = strtolower($result);
                }
                $output .= $result;
                $repeatedKey .= $keyCharacter;
                $calculation = $decrypt
                    ? "({$inputValue} - {$keyValue} + 26) mod 26 = {$resultValue}"
                    : "({$inputValue} + {$keyValue}) mod 26 = {$resultValue}";
                $steps[] = [$character, $keyCharacter, (string) $inputValue, (string) $keyValue, $calculation, $result];
            } else {
                $output .= $character;
                $repeatedKey .= '—';
                $steps[] = [$character, '—', '—', '—', 'Non-letter preserved; key does not advance', $character];
            }
        }

        return [
            'output' => $output,
            'prepared' => $input,
            'key' => $key,
            'repeated_key' => $repeatedKey,
            'steps' => $steps,
        ];
    }
}
