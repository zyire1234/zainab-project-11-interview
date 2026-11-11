<?php
require 'config.php';
page_start('Question 1: Polling Unit Result');
echo '<form method="get">'; chained_selects(true); echo '<button>Show result</button></form>';

if (!empty($_GET['pu_id'])) {
    $pu = $pdo->prepare('SELECT polling_unit_name, polling_unit_number FROM polling_unit WHERE uniqueid = ?');
    $pu->execute([(int)$_GET['pu_id']]);
    $unit = $pu->fetch();
    // announced_pu_results.polling_unit_uniqueid is a varchar holding polling_unit.uniqueid
    $st = $pdo->prepare('SELECT party_abbreviation, party_score FROM announced_pu_results WHERE polling_unit_uniqueid = ? ORDER BY party_score DESC');
    $st->execute([(string)(int)$_GET['pu_id']]);
    $rows = $st->fetchAll();
    echo '<h2>' . h($unit['polling_unit_name'] ?? '') . ' (' . h($unit['polling_unit_number'] ?? '') . ')</h2>';
    if (!$rows) echo '<div class="msg err">No results recorded for this polling unit.</div>';
    else {
        echo '<table><tr><th>Party</th><th>Score</th></tr>';
        foreach ($rows as $r) echo '<tr><td>' . h($r['party_abbreviation']) . '</td><td>' . h($r['party_score']) . '</td></tr>';
        echo '</table>';
    }
}
page_end();
