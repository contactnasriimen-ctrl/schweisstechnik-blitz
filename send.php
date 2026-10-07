<?php
/**
 * Schweisstechnik Blitz – Anfrageformular
 * Nimmt die Anfrage per POST entgegen, prüft sie und sendet sie per E-Mail.
 * Antwortet immer mit JSON: {"ok": bool, "message": string}
 *
 * Lokal (start.bat setzt BLITZ_DEV=1) wird keine Mail verschickt, sondern
 * die Nachricht unter data/outbox/ abgelegt.
 */
declare(strict_types=1);

const MAIL_TO   = 'info@schweisstechnik-blitz.de';
const MAIL_FROM = 'website@schweisstechnik-blitz.de';
const RATE_MAX  = 5;      // Anfragen pro Stunde und IP
const RATE_WIN  = 3600;

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store');

function reply(int $code, bool $ok, string $message): void
{
    http_response_code($code);
    echo json_encode(['ok' => $ok, 'message' => $message], JSON_UNESCAPED_UNICODE);
    exit;
}

function clean($value, int $max = 300): string
{
    $value = is_string($value) ? $value : '';
    $value = str_replace(["\0", "\r"], '', $value);
    $value = preg_replace('/[^\P{C}\n\t]/u', '', $value) ?? '';
    return trim(mb_substr($value, 0, $max));
}

function oneOf(string $value, array $allowed): string
{
    return in_array($value, $allowed, true) ? $value : '';
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    reply(405, false, 'Methode nicht erlaubt.');
}

// Honeypot: Bots füllen das versteckte Feld „Website“ aus → still verwerfen
if (clean($_POST['website'] ?? '') !== '') {
    reply(200, true, 'Anfrage gesendet.');
}

/* ---------- Missbrauchsschutz: IP nur gehasht, höchstens 1 Stunde ---------- */
$dataDir = __DIR__ . '/data';
if (!is_dir($dataDir)) {
    @mkdir($dataDir, 0700, true);
}
$rateFile = $dataDir . '/rate.json';
$ipHash   = hash('sha256', ($_SERVER['REMOTE_ADDR'] ?? '') . '|' . __DIR__);
$now      = time();
$fh = @fopen($rateFile, 'c+');
if ($fh) {
    flock($fh, LOCK_EX);
    $rates = json_decode(stream_get_contents($fh) ?: '{}', true) ?: [];
    foreach ($rates as $h => $stamps) {
        $rates[$h] = array_values(array_filter((array) $stamps, fn($t) => $t > $now - RATE_WIN));
        if (!$rates[$h]) {
            unset($rates[$h]);
        }
    }
    if (count($rates[$ipHash] ?? []) >= RATE_MAX) {
        flock($fh, LOCK_UN);
        fclose($fh);
        reply(429, false, 'Zu viele Anfragen in kurzer Zeit.');
    }
    $rates[$ipHash][] = $now;
    ftruncate($fh, 0);
    rewind($fh);
    fwrite($fh, json_encode($rates));
    flock($fh, LOCK_UN);
    fclose($fh);
}

/* ---------- Felder ---------- */
$arbeitenErlaubt = ['Tank oder Behälter', 'Stahlkonstruktion', 'Rohr oder Leitung', 'Bohrrohr', 'Platten oder Bleche', 'Bohren und Montage', 'Reparatur', 'Etwas anderes'];
$arbeiten = array_values(array_intersect($arbeitenErlaubt, array_map('strval', (array) ($_POST['arbeit'] ?? []))));
$ortArt   = oneOf(clean($_POST['ort_art'] ?? ''), ['Auf einer Baustelle', 'Im Betrieb oder auf dem Hof', 'Privat']);
$hoehe    = oneOf(clean($_POST['hoehe'] ?? ''), ['Nein', 'Ja, Gerüst oder Bühne nötig', 'Weiß ich nicht']);
$termin   = oneOf(clean($_POST['termin'] ?? ''), ['So bald wie möglich', 'In den nächsten zwei Wochen', 'In diesem Monat', 'Termin ist flexibel']);
$ort      = clean($_POST['ort'] ?? '', 120);
$text     = clean($_POST['beschreibung'] ?? '', 4000);
$name     = clean($_POST['name'] ?? '', 120);
$telefon  = clean($_POST['telefon'] ?? '', 40);
$email    = clean($_POST['email'] ?? '', 160);
$consent  = ($_POST['einwilligung'] ?? '') === '1';

$quelle = clean($_POST['quelle'] ?? '') === 'hero' ? 'Schnellanfrage' : 'Anfrageformular';
if (!$arbeiten) {
    if ($quelle === 'Schnellanfrage') {
        $arbeiten = ['Nicht angegeben'];
    } else {
        reply(422, false, 'Bitte wählen Sie mindestens eine Arbeit aus.');
    }
}
if ($name === '') {
    reply(422, false, 'Bitte geben Sie Ihren Namen an.');
}
if ($telefon === '' && $email === '') {
    reply(422, false, 'Bitte geben Sie eine Telefonnummer oder E-Mail-Adresse an.');
}
if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    reply(422, false, 'Bitte prüfen Sie die E-Mail-Adresse.');
}
if ($telefon !== '' && !preg_match('/^[0-9 +()\/.\-]{5,40}$/', $telefon)) {
    reply(422, false, 'Bitte prüfen Sie die Telefonnummer.');
}
if (!$consent) {
    reply(422, false, 'Bitte bestätigen Sie die Einwilligung zur Bearbeitung Ihrer Angaben.');
}

/* ---------- E-Mail ---------- */
$lines = [
    'Neue Anfrage über schweisstechnik-blitz.de (' . $quelle . ')',
    str_repeat('=', 44),
    '',
    'Arbeit:        ' . implode(', ', $arbeiten),
    'Wo:            ' . ($ortArt ?: '–'),
    'In der Höhe:   ' . ($hoehe ?: '–'),
    'PLZ und Ort:   ' . ($ort ?: '–'),
    'Wunschtermin:  ' . ($termin ?: '–'),
    '',
    'Beschreibung:',
    $text !== '' ? $text : '–',
    '',
    str_repeat('-', 44),
    'Name:          ' . $name,
    'Telefon:       ' . ($telefon ?: '–'),
    'E-Mail:        ' . ($email ?: '–'),
    '',
    'Gesendet am ' . date('d.m.Y \u\m H:i') . ' Uhr. Einwilligung zur Bearbeitung wurde erteilt.',
];
$body    = implode("\n", $lines);
$subject = 'Anfrage: ' . implode(', ', $arbeiten) . ' – ' . $name;

$headers = [
    'MIME-Version: 1.0',
    'Content-Type: text/plain; charset=UTF-8',
    'Content-Transfer-Encoding: 8bit',
    'From: ' . mb_encode_mimeheader('Website Schweisstechnik Blitz', 'UTF-8') . ' <' . MAIL_FROM . '>',
    'X-Mailer: schweisstechnik-blitz.de',
];
if ($email !== '') {
    $headers[] = 'Reply-To: ' . mb_encode_mimeheader($name, 'UTF-8') . ' <' . $email . '>';
}

if (getenv('BLITZ_DEV') === '1') {
    $outbox = $dataDir . '/outbox';
    if (!is_dir($outbox)) {
        @mkdir($outbox, 0700, true);
    }
    $ok = (bool) @file_put_contents($outbox . '/' . date('Ymd-His') . '-' . bin2hex(random_bytes(3)) . '.txt', "Subject: $subject\n\n$body");
} else {
    $ok = @mail(MAIL_TO, mb_encode_mimeheader($subject, 'UTF-8'), $body, implode("\r\n", $headers));
}

if (!$ok) {
    reply(500, false, 'Die Anfrage konnte gerade nicht gesendet werden.');
}
reply(200, true, 'Anfrage gesendet.');
