<?php
/**
 * PaySim - Unified Test Runner
 * Executes all automated test suites and outputs overall results
 */

require_once __DIR__ . '/database-test.php';
require_once __DIR__ . '/auth-test.php';
require_once __DIR__ . '/payment-test.php';
require_once __DIR__ . '/transaction-test.php';

echo "\n" . str_repeat('=', 65) . "\n";
echo "   PAYSIM AUTOMATED TEST HARNESS & SYSTEM VERIFICATION\n";
echo str_repeat('=', 65) . "\n\n";

$startTime = microtime(true);

$suites = [
    'Database & Schema' => new DatabaseTest(),
    'Auth & Lifecycle'  => new AuthTest(),
    'Payments & QR'     => new PaymentTest(),
    'Ledger & Reports'  => new TransactionTest(),
];

$allPassed = true;
$results = [];

foreach ($suites as $name => $suite) {
    $ok = $suite->run();
    $results[$name] = $ok;
    if (!$ok) {
        $allPassed = false;
    }
}

$elapsed = round(microtime(true) - $startTime, 3);

echo str_repeat('-', 65) . "\n";
echo "TEST SUITE SUMMARY EXECUTION REPORT ({$elapsed}s)\n";
echo str_repeat('-', 65) . "\n";

foreach ($results as $name => $ok) {
    $statusText = $ok ? "[PASS]" : "[FAIL]";
    echo sprintf("  %-25s : %s\n", $name, $statusText);
}

echo str_repeat('=', 65) . "\n";
if ($allPassed) {
    echo "🎉 ALL TEST SUITES PASSED PERFECTLY! System is 100% production ready.\n";
} else {
    echo "❌ SOME TESTS FAILED. Please review the output above.\n";
}
echo str_repeat('=', 65) . "\n\n";

exit($allPassed ? 0 : 1);
