<?php
require 'config.php';
header('Content-Type: application/json');
if (($_GET['type'] ?? '') === 'wards') {
    $st = $pdo->prepare('SELECT uniqueid AS value, ward_name AS label FROM ward WHERE lga_id = ? ORDER BY ward_name');
    $st->execute([(int)$_GET['lga_id']]);
} else {
    $st = $pdo->prepare("SELECT uniqueid AS value, CONCAT(COALESCE(polling_unit_name,'Unnamed'), ' (', COALESCE(polling_unit_number,'-'), ')') AS label
                         FROM polling_unit WHERE uniquewardid = ? ORDER BY polling_unit_name");
    $st->execute([(int)($_GET['ward_uid'] ?? 0)]);
}
echo json_encode($st->fetchAll());
