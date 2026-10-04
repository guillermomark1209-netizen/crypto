<?php
declare(strict_types=1);
$cipher_id = 'bifid';
$cipher_name = 'Bifid Cipher';
$cipher_description = 'Fractionate coordinates from a keyed Polybius square to mix the message.';
$cipher_rules = [
    'J is combined with I. Non-letter characters are removed from the working text.',
    'The keyword is deduplicated; remaining unused letters fill the 5 × 5 square.',
    'Encrypt each block by listing its row coordinates, then its column coordinates; regroup the sequence in pairs.',
    'Decrypt each block by flattening its ciphertext coordinate pairs, splitting the sequence in half, then pairing matching row and column entries.',
    'Period sets the block size. Leave blank to process the whole message as one block.',
];
$key_label = 'Keyword';
$key_default = 'CIPHER';
$key_hint = 'Letters A–Z only';
require __DIR__ . '/../includes/cipher_page.php';
