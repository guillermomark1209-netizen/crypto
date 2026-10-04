<?php
declare(strict_types=1);
$cipher_id = 'vigenere';
$cipher_name = 'Vigenère Cipher';
$cipher_description = 'Use a repeating alphabetic keyword to apply a different shift to each letter.';
$cipher_rules = [
    'Encrypt: Cᵢ = (Pᵢ + Kᵢ) mod 26.',
    'Decrypt: Pᵢ = (Cᵢ - Kᵢ + 26) mod 26.',
    'A = 0 through Z = 25. The keyword advances for letters only.',
    'Letter case, spaces, punctuation, and numbers are preserved.',
];
$key_label = 'Keyword';
$key_default = 'SECRET';
$key_hint = 'Letters A–Z only';
require __DIR__ . '/../includes/cipher_page.php';
