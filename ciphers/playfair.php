<?php
declare(strict_types=1);
$cipher_id = 'playfair';
$cipher_name = 'Playfair Cipher';
$cipher_description = 'Transform pairs of letters using a keyed 5 × 5 square.';
$cipher_rules = [
    'J is combined with I. Non-letter characters are removed from the working text.',
    'The keyword is deduplicated; remaining unused letters fill the square.',
    'Encryption inserts X between repeated digraph letters and pads a final single letter. A Q is used if an X filler would make XX.',
    'Same row: move right to encrypt and left to decrypt. Same column: move down or up.',
    'Rectangle: each letter moves to the other corner in its own row.',
    'Decryption keeps filler letters in the output; review the prepared digraphs to interpret them.',
];
$key_label = 'Keyword';
$key_default = 'MONARCHY';
$key_hint = 'Letters A–Z only';
require __DIR__ . '/../includes/cipher_page.php';
