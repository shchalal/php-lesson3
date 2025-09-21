<?php
declare(strict_types=1);

mb_internal_encoding('UTF-8');


function normalize_spaces(string $s): string {
    $s = trim($s);
    $s = preg_replace('/\s+/u', ' ', $s);
    return $s ?? '';
}


function mb_titlecase_words(string $s): string {
    $s = normalize_spaces($s);
    if ($s === '') return '';
    $words = preg_split('/\s+/u', $s) ?: [];
    foreach ($words as &$w) {
        $parts = explode('-', $w);
        foreach ($parts as &$p) {
            if ($p === '') continue;
            $p = mb_strtoupper(mb_substr($p, 0, 1)) . mb_strtolower(mb_substr($p, 1));
        }
        $w = implode('-', $parts);
    }
    return implode(' ', $words);
}


function initial(string $s): string {
    $s = normalize_spaces($s);
    return $s === '' ? '' : mb_strtoupper(mb_substr($s, 0, 1));
}

function get_input(): array {
    if (PHP_SAPI === 'cli') {
       
        global $argv;
        $last   = $argv[1] ?? '';
        $first  = $argv[2] ?? '';
        $middle = $argv[3] ?? '';
    } else {
     
        $last   = $_POST['last']   ?? $_GET['last']   ?? '';
        $first  = $_POST['first']  ?? $_GET['first']  ?? '';
        $middle = $_POST['middle'] ?? $_GET['middle'] ?? '';
    }
    return [$last, $first, $middle];
}

[$lastRaw, $firstRaw, $middleRaw] = get_input();

$last   = mb_titlecase_words($lastRaw);
$first  = mb_titlecase_words($firstRaw);
$middle = mb_titlecase_words($middleRaw);


$fullName = normalize_spaces(trim("$last $first $middle"));


$surnameAndInitials = $last;
$fi = initial($first);
$mi = initial($middle);
if ($fi !== '' || $mi !== '') {
    $surnameAndInitials .= ' ';
    if ($fi !== '') $surnameAndInitials .= $fi . '.';
    if ($mi !== '') $surnameAndInitials .= $mi . '.';
}


$fio = initial($last) . initial($first) . initial($middle);

$output = [
    "Полное имя: '$fullName'",
    "Фамилия и инициалы: '$surnameAndInitials'",
    "Аббревиатура: '$fio'",
];

if (PHP_SAPI !== 'cli') {
    header('Content-Type: text/html; charset=UTF-8');
    ?>
    <!doctype html>
    <meta charset="utf-8">
    <title>Стандартизатор имён</title>
    <style>
        body { font-family: system-ui, sans-serif; margin: 2rem; }
        form { display: grid; gap: .5rem; max-width: 420px; }
        label { display: grid; gap: .25rem; }
        input { padding: .5rem; font-size: 1rem; }
        .out { margin-top: 1rem; padding: .75rem; background: #f5f5f5; border-radius: .5rem; }
        code { white-space: pre-wrap; }
    </style>
    <h1>Введите Вашу Фамилию, Имя, Отчество</h1>
    <form method="post">
        <label>Фамилия <input name="last" value="<?= htmlspecialchars($lastRaw ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"></label>
        <label>Имя <input name="first" value="<?= htmlspecialchars($firstRaw ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"></label>
        <label>Отчество <input name="middle" value="<?= htmlspecialchars($middleRaw ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"></label>
        <button type="submit">Преобразовать</button>
    </form>
    <?php if ($firstRaw || $lastRaw || $middleRaw): ?>
        <div class="out"><code><?= htmlspecialchars(implode("\n", $output), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></code></div>
    <?php endif; ?>
    <?php
} else {
    echo implode(PHP_EOL, $output) . PHP_EOL;
}
