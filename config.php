<?php
// InfinityFree database settings (host version - do NOT upload this version to GitHub)
$pdo = new PDO('mysql:host=sql101.infinityfree.com;dbname=if0_43416806_zainab;charset=utf8mb4', 'if0_43416806', 'YOUR_PASSWORD', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);
const DELTA_STATE_ID = 25;

function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

function page_start($title) {
    echo '<!doctype html><html><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>' . h($title) . '</title>
<style>
 body{font-family:system-ui,sans-serif;max-width:720px;margin:2rem auto;padding:0 1rem;color:#222}
 nav a{margin-right:1rem} label{display:block;margin:1rem 0 .3rem;font-weight:600}
 select,input{width:100%;padding:.5rem;font-size:1rem;box-sizing:border-box}
 button{margin-top:1.2rem;padding:.6rem 1.4rem;font-size:1rem;cursor:pointer}
 table{border-collapse:collapse;width:100%;margin-top:1.5rem}
 th,td{border:1px solid #ccc;padding:.5rem;text-align:left} th{background:#f2f2f2}
 .msg{padding:.7rem;border-radius:4px;margin-top:1rem}
 .ok{background:#e6f7e6}.err{background:#fde8e8}
</style></head><body>
<nav><a href="index.php">Home</a><a href="q1.php">Polling unit result</a><a href="q2.php">LGA total</a><a href="q3.php">New polling unit</a></nav>
<h1>' . h($title) . '</h1>';
}
function page_end() { echo '</body></html>'; }

// Reusable chained LGA -> Ward -> Polling Unit selects
function chained_selects($withPU = true) {
    global $pdo;
    $st = $pdo->prepare('SELECT lga_id, lga_name FROM lga WHERE state_id = ? ORDER BY lga_name');
    $st->execute([DELTA_STATE_ID]);
    echo '<label>Local Government</label><select id="lga" name="lga_id" required><option value="">-- Select LGA --</option>';
    foreach ($st as $r) echo '<option value="' . h($r['lga_id']) . '">' . h($r['lga_name']) . '</option>';
    echo '</select>';
    echo '<label>Ward</label><select id="ward" name="ward_uid" required disabled><option value="">-- Select LGA first --</option></select>';
    if ($withPU) echo '<label>Polling Unit</label><select id="pu" name="pu_id" required disabled><option value="">-- Select ward first --</option></select>';
    echo '<script>
function fill(sel, items, placeholder){
  sel.innerHTML = "<option value=\"\">" + placeholder + "</option>";
  items.forEach(i => { const o = document.createElement("option"); o.value = i.value; o.textContent = i.label; sel.appendChild(o); });
  sel.disabled = items.length === 0;
}
const lga = document.getElementById("lga"), ward = document.getElementById("ward"), pu = document.getElementById("pu");
lga.addEventListener("change", async () => {
  fill(ward, [], "-- Select LGA first --"); if (pu) fill(pu, [], "-- Select ward first --");
  if (!lga.value) return;
  const d = await (await fetch("api.php?type=wards&lga_id=" + lga.value)).json();
  fill(ward, d, "-- Select Ward --");
});
if (pu) ward.addEventListener("change", async () => {
  fill(pu, [], "-- Select ward first --");
  if (!ward.value) return;
  const d = await (await fetch("api.php?type=pus&ward_uid=" + ward.value)).json();
  fill(pu, d, "-- Select Polling Unit --");
});
</script>';
}
