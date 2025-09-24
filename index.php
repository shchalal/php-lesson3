<?php
declare(strict_types=1);

mb_internal_encoding('UTF-8');


if (!extension_loaded('mbstring')) {
    fwrite(STDERR, "Ошибка: требуется расширение mbstring.\n");
    exit(1);
}


function normalize_spaces(string $s): string {
    $s = trim($s);
    $s = preg_replace('/\s+/u', ' ', $s);
    return $s ?? '';
}

function titlecase(string $s): string {
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


fwrite(STDOUT, "Введите фамилию: ");
$lastRaw = fgets(STDIN);
fwrite(STDOUT, "Введите имя: ");
$firstRaw = fgets(STDIN);
fwrite(STDOUT, "Введите отчество: ");
$middleRaw = fgets(STDIN);


if ($lastRaw === false || $firstRaw === false || $middleRaw === false) {
    fwrite(STDERR, "Ошибка ввода.\n");
    exit(1);
}


$last   = titlecase($lastRaw);
$first  = titlecase($firstRaw);
$middle = titlecase($middleRaw);

if ($last === '' || $first === '') {
    fwrite(STDERR, "Фамилия и имя не могут быть пустыми.\n");
    exit(1);
}


$fullName = trim(normalize_spaces("$last $first $middle"));

$fi = initial($first);
$mi = initial($middle);

$surnameAndInitials = $last;
if ($fi !== '' || $mi !== '') {
    $surnameAndInitials .= ' ';
    if ($fi !== '') $surnameAndInitials .= $fi . '.';
    if ($mi !== '') $surnameAndInitials .= $mi . '.';
}

$fio = initial($last) . $fi . $mi;


fwrite(STDOUT, "Полное имя: '$fullName'\n");
fwrite(STDOUT, "Фамилия и инициалы: '$surnameAndInitials'\n");
fwrite(STDOUT, "Аббревиатура: '$fio'\n");
