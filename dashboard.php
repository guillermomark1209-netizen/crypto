<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
require_login();

$ciphers = [
    ['name' => 'Caesar Cipher', 'description' => 'A simple shift substitution. Move every letter by a fixed number of places.', 'href' => 'ciphers/caesar.php', 'icon' => '↻', 'tag' => 'SUBSTITUTION'],
    ['name' => 'Vigenère Cipher', 'description' => 'A repeating keyword adds a different shift to each letter in your message.', 'href' => 'ciphers/vigenere.php', 'icon' => 'Aa', 'tag' => 'POLYALPHABETIC'],
    ['name' => 'Playfair Cipher', 'description' => 'Encrypt pairs of letters with a keyed 5 × 5 matrix.', 'href' => 'ciphers/playfair.php', 'icon' => '▦', 'tag' => 'DIGRAPH'],
    ['name' => 'Bifid Cipher', 'description' => 'Combine Polybius coordinates to mix letters across each block.', 'href' => 'ciphers/bifid.php', 'icon' => '⇄', 'tag' => 'FRACTIONATING'],
];

$page_title = 'Dashboard';
require __DIR__ . '/includes/header.php';
?>
<main class="page-shell">
    <section class="welcome-row">
        <div>
            <span class="eyebrow">Your cryptography workspace</span>
            <h1>Welcome to the lab, <?= e($_SESSION['user']['name']) ?>.</h1>
            <p class="text-muted">Choose a cipher to explore how a secret message takes shape.</p>
        </div>
        <div class="welcome-mark" aria-hidden="true">⌘</div>
    </section>

    <section class="cipher-grid" aria-label="Available ciphers">
        <?php foreach ($ciphers as $index => $cipher): ?>
            <article class="cipher-card panel">
                <div class="cipher-card-top"><span class="cipher-icon"><?= e($cipher['icon']) ?></span><span class="cipher-tag"><?= e($cipher['tag']) ?></span></div>
                <span class="card-index">0<?= $index + 1 ?></span>
                <h2><?= e($cipher['name']) ?></h2>
                <p class="text-muted"><?= e($cipher['description']) ?></p>
                <a class="button button-light" href="<?= e($cipher['href']) ?>">Open cipher <span aria-hidden="true">↗</span></a>
            </article>
        <?php endforeach; ?>
    </section>

    <section class="note-banner">
        <span class="note-icon" aria-hidden="true">✳</span>
        
        <div><strong>Made for learning, not for securing secrets.</strong><p>This Ciphers are Created by Mark Guillermo From Irene B. Antonio College of Mindanao</p></div>
   
    </section>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
