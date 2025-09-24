<?php
declare(strict_types=1);
mb_internal_encoding('UTF-8');


function normalize_spaces(string $s): string {
    $s = trim($s, " \t\r\n\0\x0B");
    if ($s === '') return '';
    $parts = preg_split('/[ \t\r\n\0\x0B]+/', $s);
    return implode(' ', array_filter($parts ?: [$s], fn($p) => $p !== ''));
}
function titlecase(string $s): string {
    $s = normalize_spaces($s);
    if ($s === '') return '';
    $words = explode(' ', $s);
    foreach ($words as &$w) {
        $parts = explode('-', $w);
        foreach ($parts as &$p) {
            if ($p !== '') {
                $p = mb_strtoupper(mb_substr($p, 0, 1), 'UTF-8')
                   . mb_strtolower(mb_substr($p, 1), 'UTF-8');
            }
        }
        $w = implode('-', $parts);
    }
    return implode(' ', $words);
}
function initial(string $s): string {
    $s = normalize_spaces($s);
    return $s === '' ? '' : mb_strtoupper(mb_substr($s, 0, 1), 'UTF-8');
}
function looks_broken_stdin(string $s): bool {
    
    $len = strlen($s);
    if ($len === 0) return false;
    for ($i = 0; $i < $len; $i++) {
        $b = ord($s[$i]);
        if ($b !== 0x00 && $b !== 0x0D && $b !== 0x0A) return false;
    }
    return true;
}
function ask(string $prompt): string {
    fwrite(STDOUT, $prompt);
    fflush(STDOUT);
    if (function_exists('readline')) {
        $line = readline('');
        return ($line === false ? '' : $line) . "\n";
    }
    $line = fgets(STDIN);
    return $line === false ? '' : $line;
}


$lastRaw   = ask("Введите фамилию: ");
$firstRaw  = ask("Введите имя: ");
$middleRaw = ask("Введите отчество: ");

if (looks_broken_stdin($lastRaw) || looks_broken_stdin($firstRaw)) {
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
$surnameAndInitials = $last . (($fi || $mi) ? " $fi." . ($mi ? "$mi." : '') : '');
$fio = initial($last) . $fi . $mi;


fwrite(STDOUT, "Полное имя: '$fullName'\n");
fwrite(STDOUT, "Фамилия и инициалы: '$surnameAndInitials'\n");
fwrite(STDOUT, "Аббревиатура: '$fio'\n");
