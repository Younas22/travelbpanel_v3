<?php
/**
 * Copy this file to deploy-config.php (same folder) on the SERVER only,
 * and fill in the two values below. deploy-config.php is gitignored —
 * never commit it, since DEPLOY_SECRET is a real secret.
 */

// Absolute path to the git-cloned app on the server, e.g.:
// /home/USERNAME/travelbpanel_v3
define('REPO_PATH', '/home/USERNAME/travelbpanel_v3');

// A long random string — used both to verify the GitHub webhook signature
// and as the ?token= for manual triggering. Generate one with:
//   php -r "echo bin2hex(random_bytes(32)), PHP_EOL;"
define('DEPLOY_SECRET', 'REPLACE_ME_WITH_A_LONG_RANDOM_SECRET');
