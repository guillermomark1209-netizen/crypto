<?php
declare(strict_types=1);
$cipher_id = 'caesar';
$cipher_name = 'Caesar Cipher';
$cipher_description = 'Shift each letter by a fixed number of places around the alphabet.';
$cipher_rules = [
    'Encrypt: E(x) = (x + k) mod 26.',
    'Decrypt: D(x) = (x - k + 26) mod 26.',
    'A = 0 through Z = 25. Choose an integer shift from 0 to 25.',
    'Letter case, spaces, punctuation, and numbers are preserved.',
];
$key_label = 'Shift value';
$key_default = '3';
$key_hint = 'Whole number from 0 to 25';
require __DIR__ . '/../includes/cipher_page.php';
