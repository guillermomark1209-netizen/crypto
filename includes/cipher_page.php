<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../algorithms/CaesarCipher.php';
require_once __DIR__ . '/../algorithms/VigenereCipher.php';
require_once __DIR__ . '/../algorithms/PlayfairCipher.php';
require_once __DIR__ . '/../algorithms/BifidCipher.php';

$allowedCiphers = ['caesar', 'vigenere', 'playfair', 'bifid'];
if (!in_array($cipher_id, $allowedCiphers, true)) {
    http_response_code(404);
    exit('Cipher not found.');
}

$error = '';
$result = null;
$operation = post_string('operation', 'encrypt');
$input = post_string('input');
$key = post_string('key', $key_default);
$period = post_string('period');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();
    if (!in_array($operation, ['encrypt', 'decrypt'], true)) {
        $error = 'Choose Encrypt or Decrypt.';
    } else {
        try {
            $result = match ($cipher_id) {
                'caesar' => CaesarCipher::calculate($input, $key, $operation),
                'vigenere' => VigenereCipher::calculate($input, $key, $operation),
                'playfair' => PlayfairCipher::calculate($input, $key, $operation),
                'bifid' => BifidCipher::calculate($input, $key, $operation, $period),
            };
        } catch (InvalidArgumentException $exception) {
            $error = $exception->getMessage();
        } catch (Throwable $exception) {
            error_log('CipherLab calculation error: ' . $exception->getMessage());
            $error = 'Unable to calculate this message. Check the input and try again.';
        }
    }
}

$page_title = $cipher_name;
require __DIR__ . '/header.php';
?>
<main class="page-shell">
    <a class="back-link" href="../dashboard.php"><span aria-hidden="true">←</span> All ciphers</a>
    <section class="cipher-heading">
        <span class="eyebrow">Interactive cipher · <?= e($cipher_id) ?></span>
        <h1><?= e($cipher_name) ?></h1>
        <p class="text-muted"><?= e($cipher_description) ?></p>
    </section>
    <div class="workbench-grid">
        <section class="panel input-panel">
            <div class="section-heading"><span class="step-number">01</span><div><h2>Your message</h2><p>Choose an operation, then enter your text and key.</p></div></div>
            <?php if ($error !== ''): ?><div class="notice error" role="alert"><?= e($error) ?></div><?php endif; ?>
            <form method="post">
                <?= csrf_field() ?>
                <label for="operation">Operation</label>
                <select id="operation" name="operation">
                    <option value="encrypt" <?= $operation === 'encrypt' ? 'selected' : '' ?>>Encrypt</option>
                    <option value="decrypt" <?= $operation === 'decrypt' ? 'selected' : '' ?>>Decrypt</option>
                </select>
                <label for="input">Input text</label>
                <textarea id="input" name="input" rows="5" maxlength="2000" required placeholder="Type a message up to 2,000 bytes…"><?= e($input) ?></textarea>
                <div class="field-row"><label for="key"><?= e($key_label) ?></label><span class="label-hint"><?= e($key_hint) ?></span></div>
                <input id="key" name="key" value="<?= e($key) ?>" maxlength="<?= $cipher_id === 'caesar' ? '2' : '100' ?>" <?= $cipher_id === 'caesar' ? 'type="number" min="0" max="25" step="1"' : 'pattern="[A-Za-z]+"' ?> required>
                <?php if ($cipher_id === 'bifid'): ?>
                    <label for="period">Period / block size <span class="label-hint">(optional)</span></label>
                    <input id="period" name="period" type="number" min="1" max="2000" value="<?= e($period) ?>" placeholder="Blank = entire message">
                <?php endif; ?>
                <button class="button button-primary button-wide" type="submit">Show the steps <span aria-hidden="true">→</span></button>
            </form>
        </section>
        <aside class="panel rules-panel">
            <div class="section-heading"><span class="step-number step-number-soft">i</span><div><h2>How it works</h2><p>The rules behind this cipher.</p></div></div>
            <ul class="rules-list"><?php foreach ($cipher_rules as $rule): ?><li><?= e($rule) ?></li><?php endforeach; ?></ul>
        </aside>
    </div>

    <?php if ($result !== null): ?>
        <section class="panel result-summary">
            <div class="result-title"><div><span class="eyebrow">Your calculation</span><h2>Message summary</h2></div><span class="operation-pill"><?= e(ucfirst($operation)) ?></span></div>
            <dl class="summary-grid">
                <div><dt>Cipher</dt><dd><?= e($cipher_name) ?></dd></div>
                <div><dt>Operation</dt><dd><?= e(ucfirst($operation)) ?></dd></div>
                <div><dt>Input</dt><dd><?= e($input) ?></dd></div>
                <div><dt>Key</dt><dd><?= e((string) $result['key']) ?></dd></div>
                <div><dt>Prepared input</dt><dd><?= e((string) $result['prepared']) ?></dd></div>
                <?php if ($cipher_id === 'vigenere'): ?><div><dt>Repeated keyword</dt><dd><?= e($result['repeated_key']) ?></dd></div><?php endif; ?>
                <?php if ($cipher_id === 'bifid'): ?><div><dt>Period</dt><dd><?= e((string) $result['period']) ?></dd></div><?php endif; ?>
            </dl>
        </section>

        <section class="panel">
            <div class="section-heading"><span class="step-number">02</span><div><h2><?= $cipher_id === 'caesar' ? 'Alphabet mapping' : ($cipher_id === 'vigenere' ? 'Vigenère table · Tabula Recta' : 'Generated 5 × 5 matrix') ?></h2><p>Built from your selected operation and key.</p></div></div>
            <?php if ($cipher_id === 'caesar'): ?>
                <?php
                $alphabet = range('A', 'Z');
                $shiftedAlphabet = [];
                foreach ($alphabet as $index => $letter) {
                    $shiftedAlphabet[] = chr(65 + ($index + (int) $result['key']) % 26);
                }
                render_table(['Mapping', ...$alphabet], [['Alphabet', ...$shiftedAlphabet]], 'alphabet-table');
                ?>
                <p class="table-caption">Encryption reads left to right. Decryption applies the same mapping in reverse.</p>
            <?php elseif ($cipher_id === 'vigenere'): ?>
                <?php
                $tabulaRows = [];
                for ($row = 0; $row < 26; $row++) {
                    $tabulaRow = [chr(65 + $row)];
                    for ($column = 0; $column < 26; $column++) {
                        $tabulaRow[] = chr(65 + ($row + $column) % 26);
                    }
                    $tabulaRows[] = $tabulaRow;
                }
                render_table(['Key ↓ / Input →', ...range('A', 'Z')], $tabulaRows, 'tabula-table');
                ?>
                <p class="table-caption">Rows represent key letters; columns represent input letters.</p>
            <?php else: ?>
                <p class="table-caption">Coordinates are shown as (row, column). I and J share one cell.</p>
                <?php
                $matrixRows = [];
                for ($row = 1; $row <= 5; $row++) {
                    $matrixRow = [(string) $row];
                    for ($column = 1; $column <= 5; $column++) {
                        $matrixRow[] = square_cell($result['square'], $row, $column);
                    }
                    $matrixRows[] = $matrixRow;
                }
                render_table(['r / c', '1', '2', '3', '4', '5'], $matrixRows, 'matrix-table');
                ?>
            <?php endif; ?>
        </section>

        <section class="panel">
            <div class="section-heading"><span class="step-number">03</span><div><h2>Step-by-step process</h2><p>Follow how each letter or block is transformed.</p></div></div>
            <?php if ($cipher_id === 'caesar'): ?>
                <?php render_table(['Input', 'Input value', 'Calculation', 'Result'], $result['steps']); ?>
            <?php elseif ($cipher_id === 'vigenere'): ?>
                <?php render_table(['Input', 'Key', 'Input value', 'Key value', 'Calculation', 'Result'], $result['steps']); ?>
            <?php elseif ($cipher_id === 'playfair'): ?>
                <?php render_table(['Digraph', 'Letter positions', 'Rule used', 'Transformation'], $result['steps']); ?>
            <?php else: ?>
                <?php foreach ($result['blocks'] as $block): ?>
                    <article class="block-detail">
                        <h3>Block <?= e((string) $block['number']) ?> <span><?= e($block['input']) ?> → <?= e($block['result']) ?></span></h3>
                        <dl class="summary-grid block-grid">
                            <div><dt>Letter coordinates</dt><dd><?= e($block['coordinates']) ?></dd></div>
                            <div><dt>Row sequence</dt><dd><?= e($block['rows']) ?></dd></div>
                            <div><dt>Column sequence</dt><dd><?= e($block['columns']) ?></dd></div>
                            <div><dt><?= $operation === 'encrypt' ? 'Combined rows + columns' : 'Flattened coordinates' ?></dt><dd><?= e($block['combined']) ?></dd></div>
                            <div><dt>Regrouped coordinates → letters</dt><dd><?= e($block['regrouped']) ?></dd></div>
                        </dl>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>

        <section class="final-result">
            <div><span class="eyebrow">04 · Finished</span><h2>Final result</h2><p id="cipher-result" class="cipher-output"><?= e($result['output']) ?></p></div>
            <div class="copy-area"><button class="button button-light" id="copy-result" type="button">Copy result</button><span id="copy-status" class="copy-status" role="status" aria-live="polite"></span></div>
        </section>
    <?php endif; ?>
</main>
<?php require __DIR__ . '/footer.php'; ?>
