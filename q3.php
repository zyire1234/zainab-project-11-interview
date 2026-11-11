<?php
require 'config.php';
page_start('Question 3: Add New Polling Unit Results');
$parties = $pdo->query('SELECT partyid FROM party ORDER BY id')->fetchAll(PDO::FETCH_COLUMN);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $wardUid = (int)$_POST['ward_uid'];
        $name = trim($_POST['pu_name'] ?? ''); $number = trim($_POST['pu_number'] ?? '');
        $by = trim($_POST['entered_by'] ?? ''); $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        if ($name === '' || $by === '' || !$wardUid) throw new Exception('Please fill in all required fields.');

        $w = $pdo->prepare('SELECT ward_id, lga_id FROM ward WHERE uniqueid = ?');
        $w->execute([$wardUid]); $ward = $w->fetch();
        if (!$ward) throw new Exception('Invalid ward.');

        $pdo->beginTransaction();   // all-or-nothing: unit + all party scores
        $next = $pdo->prepare('SELECT COALESCE(MAX(polling_unit_id),0)+1 FROM polling_unit WHERE ward_id = ? AND lga_id = ?');
        $next->execute([$ward['ward_id'], $ward['lga_id']]);
        $ins = $pdo->prepare('INSERT INTO polling_unit (polling_unit_id, ward_id, lga_id, uniquewardid, polling_unit_number, polling_unit_name, entered_by_user, date_entered, user_ip_address)
                              VALUES (?,?,?,?,?,?,?,NOW(),?)');
        $ins->execute([$next->fetchColumn(), $ward['ward_id'], $ward['lga_id'], $wardUid, $number, $name, $by, $ip]);
        $puId = $pdo->lastInsertId();

        $r = $pdo->prepare('INSERT INTO announced_pu_results (polling_unit_uniqueid, party_abbreviation, party_score, entered_by_user, date_entered, user_ip_address) VALUES (?,?,?,?,NOW(),?)');
        foreach ($parties as $p) {
            $score = $_POST['score'][$p] ?? '';
            if (!ctype_digit((string)$score)) throw new Exception("Enter a valid whole number for $p.");
            $r->execute([(string)$puId, $p, (int)$score, $by, $ip]);
        }
        $pdo->commit();
        echo '<div class="msg ok">Saved! New polling unit ID: ' . h($puId) . '. <a href="q1.php">View it</a></div>';
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        echo '<div class="msg err">' . h($e->getMessage()) . '</div>';
    }
}
?>
<form method="post">
<?php chained_selects(false); ?>
<label>Polling Unit Name</label><input name="pu_name" required>
<label>Polling Unit Number</label><input name="pu_number" placeholder="e.g. DT1708006">
<label>Entered by</label><input name="entered_by" required>
<h3>Party scores</h3>
<?php foreach ($parties as $p): ?>
<label><?= h($p) ?></label><input type="number" min="0" name="score[<?= h($p) ?>]" value="0" required>
<?php endforeach; ?>
<button>Save results</button>
</form>
<?php page_end();
