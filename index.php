cat >/var/www/sitebaffa/index.php <<'PHP'
<?php
/* ==========
   INDEX DE TESTE / DIAGNÓSTICO
   Remova este arquivo em produção.
========== */

// (Opcional) Preencha para testar o MySQL:
const DB_HOST = 'localhost';
const DB_USER = '';
const DB_PASS = '';
const DB_NAME = '';

if (isset($_GET['phpinfo'])) { phpinfo(); exit; }

function badge($ok) {
  return $ok
    ? '<span class="ok">OK</span>'
    : '<span class="fail">FAIL</span>';
}

$httpsOn   = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443;
$docRoot   = $_SERVER['DOCUMENT_ROOT'] ?? '';
$scriptDir = __DIR__;
$canWrite  = is_writable($scriptDir);
$phpVer    = PHP_VERSION;
$server    = $_SERVER['SERVER_SOFTWARE'] ?? '';
$serverName= $_SERVER['SERVER_NAME'] ?? '';
$serverIP  = $_SERVER['SERVER_ADDR'] ?? '';
$clientIP  = $_SERVER['REMOTE_ADDR'] ?? '';

$apacheMods = function_exists('apache_get_modules') ? @apache_get_modules() : null;

// Teste MySQL (apenas se credenciais preenchidas)
$dbTestMsg = '—';
$dbOk = null;
if (DB_USER !== '' && DB_NAME !== '') {
  mysqli_report(MYSQLI_REPORT_OFF);
  $mysqli = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
  if ($mysqli && !$mysqli->connect_errno) {
    $dbOk = true;
    $res = $mysqli->query('SELECT NOW() as agora');
    $row = $res ? $res->fetch_assoc() : null;
    $dbTestMsg = 'Conectado. NOW() = ' . ($row['agora'] ?? '—');
    $mysqli->close();
  } else {
    $dbOk = false;
    $dbTestMsg = 'Erro: ' . ($mysqli ? $mysqli->connect_error : 'sem detalhes');
  }
}

// Teste de escrita: cria e apaga um arquivo
$writeMsg = '';
if ($canWrite) {
  $testFile = $scriptDir . '/_write_test_' . uniqid() . '.txt';
  $okCreate = @file_put_contents($testFile, 'ok ' . date('c')) !== false;
  $okDelete = $okCreate ? @unlink($testFile) : false;
  $canWrite = $okCreate && $okDelete;
  $writeMsg = $canWrite ? 'Consegui criar e remover arquivo.' : 'Falhei em criar/remover arquivo.';
} else {
  $writeMsg = 'Diretório não é gravável.';
}
?>
<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <title>Site Baffa — Diagnóstico</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    :root { --bg:#0f172a; --card:#111827; --muted:#9ca3af; --txt:#e5e7eb; --good:#10b981; --bad:#ef4444; --warn:#f59e0b; --link:#60a5fa; }
    * { box-sizing: border-box; }
    body { margin:0; background:linear-gradient(135deg,#0b1220,#101826 60%); color:var(--txt); font:15px/1.45 system-ui, -apple-system, Segoe UI, Roboto, Ubuntu, Cantarell, "Helvetica Neue", Arial; }
    .wrap { max-width:1000px; margin:40px auto; padding:0 20px; }
    h1 { margin:0 0 8px; font-size:26px; }
    .muted { color:var(--muted); }
    .grid { display:grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap:16px; margin-top:18px; }
    .card { background:rgba(17,24,39,.72); border:1px solid rgba(255,255,255,.06); border-radius:16px; padding:16px; backdrop-filter: blur(6px); }
    .row { display:flex; justify-content:space-between; gap:16px; padding:8px 0; border-bottom:1px dashed rgba(255,255,255,.08); }
    .row:last-child { border-bottom:0; }
    code, .mono { font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, "Liberation Mono", monospace; }
    a { color:var(--link); text-decoration: none; }
    a:hover { text-decoration: underline; }
    .ok, .fail, .warn { font-weight:700; padding:.2rem .5rem; border-radius:999px; }
    .ok { background: color-mix(in srgb, var(--good) 16%, transparent); color:#bbf7d0; border:1px solid color-mix(in srgb, var(--good) 40%, transparent); }
    .fail { background: color-mix(in srgb, var(--bad) 16%, transparent); color:#fecaca; border:1px solid color-mix(in srgb, var(--bad) 40%, transparent); }
    .warn { background: color-mix(in srgb, var(--warn) 16%, transparent); color:#fde68a; border:1px solid color-mix(in srgb, var(--warn) 40%, transparent); }
    .btns { display:flex; gap:10px; flex-wrap:wrap; margin-top:12px; }
    .btn { display:inline-block; padding:10px 14px; border-radius:10px; border:1px solid rgba(255,255,255,.12); background:#0b1220; color:#fff; text-decoration:none; }
    .btn:hover { background:#0f172a; }
    footer { margin:24px 0 0; color:var(--muted); font-size:13px; }
  </style>
</head>
<body>
  <div class="wrap">
    <h1>✅ Site Baffa funcionando via HTTPS - nada?</h1>
    <div class="muted">Diagnóstico rápido do servidor e ambiente PHP/Apache.</div>

    <div class="grid">
      <div class="card">
        <h3>Servidor</h3>
        <div class="row"><span>Server software</span><span class="mono"><?= htmlspecialchars($server) ?></span></div>
        <div class="row"><span>Server Name</span><span class="mono"><?= htmlspecialchars($serverName) ?></span></div>
        <div class="row"><span>Server IP</span><span class="mono"><?= htmlspecialchars($serverIP) ?></span></div>
        <div class="row"><span>Client IP</span><span class="mono"><?= htmlspecialchars($clientIP) ?></span></div>
        <div class="row"><span>HTTPS ativo</span><span><?= badge($httpsOn) ?></span></div>
        <div class="row"><span>DocumentRoot</span><span class="mono"><?= htmlspecialchars($docRoot) ?></span></div>
        <div class="row"><span>Diretório do site</span><span class="mono"><?= htmlspecialchars($scriptDir) ?></span></div>
      </div>

      <div class="card">
        <h3>PHP</h3>
        <div class="row"><span>Versão do PHP</span><span class="mono"><?= htmlspecialchars($phpVer) ?></span></div>
        <div class="row"><span>Permissão de escrita</span><span><?= badge($canWrite) ?></span></div>
        <div class="row"><span>Teste de escrita</span><span class="mono"><?= htmlspecialchars($writeMsg) ?></span></div>
        <div class="btns">
          <a class="btn" href="?phpinfo=1" target="_blank" rel="noopener">Abrir phpinfo()</a>
        </div>
      </div>

      <div class="card">
        <h3>MySQL (opcional)</h3>
        <div class="row"><span>Credenciais definidas</span>
          <span><?= badge(DB_USER !== '' && DB_NAME !== '') ?></span>
        </div>
        <div class="row"><span>Status conexão</span>
          <span><?= is_null($dbOk) ? '<span class="warn">PULAR TESTE</span>' : badge($dbOk) ?></span>
        </div>
        <div class="row"><span>Mensagem</span><span class="mono"><?= htmlspecialchars($dbTestMsg) ?></span></div>
        <div style="margin-top:8px" class="muted mono">
          Edite <strong>index.php</strong> e preencha DB_USER/DB_PASS/DB_NAME para testar.
        </div>
      </div>

      <div class="card">
        <h3>Apache</h3>
        <div class="row"><span>mod_rewrite</span>
          <span><?= badge(function_exists('apache_get_modules') ? in_array('mod_rewrite', $apacheMods ?? []) : true) ?></span>
        </div>
        <div class="row"><span>mod_ssl</span>
          <span><?= badge(function_exists('apache_get_modules') ? in_array('ssl_module', $apacheMods ?? []) : true) ?></span>
        </div>
        <div class="row"><span>phpMyAdmin</span>
          <span><a href="/phpmyadmin" target="_blank" rel="noopener">/phpmyadmin</a></span>
        </div>
      </div>
    </div>

    <footer>
      Remova este arquivo após os testes. —  TESTE<?= date('Y-m-d H:i:s') ?>
    </footer>
  </div>
</body>
</html>
PHP
chown -R www-data:www-data /var/www/sitebaffa
