<?php
require 'config.php';
page_start('Question 2: Total Result by LGA');
$lgas = $pdo->prepare('SELECT lga_id, lga_name FROM lga WHERE state_id = ? ORDER BY lga_name');
$lgas->execute([DELTA_STATE_ID]);
$sel = (int)($_GET['lga_id'] ?? 0);
echo '<form method="get"><label>Local Government</label><select name="lga_id" onchange="this.form.submit()" required><option value="">-- Select LGA --</option>';
foreach ($lgas as $l) echo '<option value="' . $l['lga_id'] . '"' . ($sel == $l['lga_id'] ? ' selected' : '') . '>' . h($l['lga_name']) . '</option>';
echo '</select><noscript><button>Show</button></noscript></form>';

if ($sel) {
    // Sum polling-unit results (NOT announced_lga_results)
    $st = $pdo->prepare('SELECT r.party_abbreviation, SUM(r.party_score) AS total
        FROM announced_pu_results r
        JOIN polling_unit p ON p.uniqueid = CAST(r.polling_unit_uniqueid AS UNSIGNED)
        WHERE p.lga_id = ?
        GROUP BY r.party_abbreviation ORDER BY total DESC');
    $st->execute([$sel]);
    $rows = $st->fetchAll();
    if (!$rows) echo '<div class="msg err">No polling unit results found for this LGA.</div>';
    else {
        echo '<table><tr><th>Party</th><th>Total score</th></tr>';
        foreach ($rows as $r) echo '<tr><td>' . h($r['party_abbreviation']) . '</td><td>' . number_format($r['total']) . '</td></tr>';
        echo '</table>';
    }
}
page_end();
