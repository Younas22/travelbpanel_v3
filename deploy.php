<?php
/**
 * One-click / webhook deploy script.
 *
 * Two ways to trigger it:
 *   1. Manually — open in a browser:
 *        https://your-domain.com/deploy.php?token=YOUR_SECRET
 *   2. Automatically — a GitHub webhook POSTs here on every push to `main`,
 *      signed with the same secret (verified via X-Hub-Signature-256).
 *
 * Either way it just runs `git pull origin main` in the repo folder and
 * prints/logs the output. It does NOT run composer install, migrations,
 * or cache clears — those stay manual for now (safer default).
 *
 * SETUP (do this once on the server):
 *   1. Copy deploy-config.example.php to deploy-config.php (same folder)
 *      and fill in REPO_PATH and DEPLOY_SECRET. deploy-config.php is
 *      gitignored on purpose — it holds a secret and must never be
 *      committed.
 *   2. Make sure this file (deploy.php) lives at the webroot of the app
 *      that Git Version Control cloned, i.e.
 *      /home/USERNAME/travelbpanel_v3/deploy.php, so it ships to
 *      /home/USERNAME/travelbpanel_v3/public/deploy.php only if you
 *      symlink/copy it there — see the README note at the bottom of this
 *      file for the two ways to expose it publicly.
 */

$configFile = __DIR__ . '/deploy-config.php';

if (!file_exists($configFile)) {
    http_response_code(500);
    exit("deploy-config.php not found. Copy deploy-config.example.php to deploy-config.php and fill it in.\n");
}

require $configFile; // must define REPO_PATH and DEPLOY_SECRET

function deployLog(string $line): void
{
    $logFile = REPO_PATH . '/deploy.log';
    $stamp   = date('Y-m-d H:i:s');
    @file_put_contents($logFile, "[{$stamp}] {$line}\n", FILE_APPEND | LOCK_EX);
}

function fail(int $code, string $message): never
{
    http_response_code($code);
    deployLog("REJECTED ({$code}): {$message}");
    exit($message . "\n");
}

$rawBody   = file_get_contents('php://input');
$isWebhook = isset($_SERVER['HTTP_X_HUB_SIGNATURE_256']);

if ($isWebhook) {
    // GitHub webhook path — verify the HMAC signature.
    $signature = $_SERVER['HTTP_X_HUB_SIGNATURE_256'];
    $expected  = 'sha256=' . hash_hmac('sha256', $rawBody, DEPLOY_SECRET);

    if (!hash_equals($expected, $signature)) {
        fail(403, 'Invalid webhook signature.');
    }

    // Optional: only deploy on pushes to main.
    $payload = json_decode($rawBody, true);
    $ref     = $payload['ref'] ?? '';
    if ($ref !== '' && $ref !== 'refs/heads/main') {
        deployLog("Ignored push to {$ref} (not main).");
        exit("Ignored — not a push to main.\n");
    }
} else {
    // Manual browser click path — verify the ?token= query param.
    $token = $_GET['token'] ?? '';
    if (!hash_equals(DEPLOY_SECRET, $token)) {
        fail(403, 'Invalid or missing token.');
    }
}

if (!is_dir(REPO_PATH)) {
    fail(500, 'REPO_PATH does not exist: ' . REPO_PATH);
}

$command = sprintf(
    'cd %s && git pull origin main 2>&1',
    escapeshellarg(REPO_PATH)
);

$output = shell_exec($command);
deployLog("git pull output:\n" . $output);

header('Content-Type: text/plain');
echo "Deploy finished.\n\n" . $output;

/**
 * HOW TO EXPOSE THIS FILE PUBLICLY
 * ---------------------------------
 * Laravel's webroot is the `public/` folder, but this file lives at the
 * repo root (next to artisan) so `git pull` can update itself safely and
 * so it's outside the webroot by default (extra safety — nobody can hit
 * it unless you deliberately expose it). Pick ONE:
 *
 *   A. Symlink it into public/ (recommended):
 *        cd /home/USERNAME/travelbpanel_v3/public
 *        ln -s ../deploy.php deploy.php
 *      Then the URL is: https://demo.travelbookingpanel.com/deploy.php
 *
 *   B. Copy deploy.php + deploy-config.php into public/ directly instead
 *      of symlinking (works the same, just remember deploy-config.php
 *      must also be copied there and is still gitignored).
 */
