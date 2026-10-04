<?php
declare(strict_types=1);

function e(string|int|null $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function post_string(string $key, string $default = ''): string
{
    $value = $_POST[$key] ?? $default;
    return is_string($value) ? $value : $default;
}

function is_logged_in(): bool
{
    return isset($_SESSION['user']['id'], $_SESSION['user']['name']);
}

function require_login(): void
{
    if (!is_logged_in()) {
        $prefix = str_contains($_SERVER['SCRIPT_NAME'] ?? '', '/ciphers/') ? '../' : '';
        header('Location: ' . $prefix . 'index.php');
        exit;
    }
}

function csrf_token(): string
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf_token(): void
{
    $submitted = post_string('csrf_token');
    if ($submitted === '' || !hash_equals(csrf_token(), $submitted)) {
        http_response_code(403);
        exit('The security token is invalid. Reload the page and try again.');
    }
}

function render_table(array $headers, array $rows, string $class = ''): void
{
    echo '<div class="table-scroll"><table' . ($class !== '' ? ' class="' . e($class) . '"' : '') . '><thead><tr>';
    foreach ($headers as $header) {
        echo '<th scope="col">' . e($header) . '</th>';
    }
    echo '</tr></thead><tbody>';
    foreach ($rows as $row) {
        echo '<tr>';
        foreach ($row as $cell) {
            echo '<td>' . e((string) $cell) . '</td>';
        }
        echo '</tr>';
    }
    echo '</tbody></table></div>';
}

function alphabet_square(string $keyword): array
{
    $alphabet = 'ABCDEFGHIKLMNOPQRSTUVWXYZ';
    $keyword = str_replace('J', 'I', strtoupper($keyword));
    $letters = [];
    foreach (str_split($keyword . $alphabet) as $letter) {
        if ($letter >= 'A' && $letter <= 'Z' && $letter !== 'J' && !in_array($letter, $letters, true)) {
            $letters[] = $letter;
        }
    }
    $square = implode('', $letters);
    $positions = [];
    foreach ($letters as $index => $letter) {
        $positions[$letter] = [intdiv($index, 5) + 1, $index % 5 + 1];
    }
    return [$square, $positions];
}

function square_cell(string $square, int $row, int $column): string
{
    return $square[($row - 1) * 5 + $column - 1];
}
