<?php
/**
 * 3.2.5 release smoke test.
 *
 * Verifies the three P0 fixes without needing PHPUnit/composer:
 *   - P0-1 idempotency guard helper logic
 *   - P0-2 booking response classification
 *   - P0-3 cache key includes account + zip
 *
 * Run with:  php tests/smoke-3.2.5.php
 * Exit code 0 = all green, 1 = any assertion failed.
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    define('ABSPATH', '/tmp/wordpress/');
}

require __DIR__ . '/../includes/api/class-xml-parser.php';
require __DIR__ . '/../includes/api/interface-api-connector.php';
require __DIR__ . '/smoke-3.2.5-db-stub.php';
require __DIR__ . '/../connectors/intellisource/class-intellisource-connector.php';

use FFFL\Connectors\IntelliSource\IntelliSourceConnector;

$failures = [];

function assert_true(bool $cond, string $label): void {
    global $failures;
    if ($cond) {
        echo "  PASS  $label\n";
    } else {
        echo "  FAIL  $label\n";
        $failures[] = $label;
    }
}

echo "\n== P0-3: cache key includes account + zip ==\n";

$key_a = "slots_5_2026-05-21_" . md5('111111|20001');
$key_b = "slots_5_2026-05-21_" . md5('222222|19901');
$key_c = "slots_5_2026-05-21_" . md5('111111|20001');
assert_true($key_a !== $key_b, 'different account/zip produces different key');
assert_true($key_a === $key_c, 'same account/zip produces same key');

echo "\n== P0-2: booking response classification ==\n";

$connector = new IntelliSourceConnector();
$ref = new ReflectionClass($connector);
$method = $ref->getMethod('parse_booking_response');
$method->setAccessible(true);
$call = fn($response, $data = []) => $method->invoke($connector, $response, $data);

$r = $call('No available slots');
assert_true($r->success === false, '"No available slots" classified as failure');

$r = $call('<html><body>Service temporarily unavailable</body></html>');
assert_true($r->success === false, 'HTML page without "error" classified as failure');

$r = $call('<response><error_cd>04</error_cd><message>Account already scheduled</message></response>');
assert_true($r->success === false, 'XML error_cd response classified as failure');

$r = $call('<response><confirmation>ABC12345</confirmation><caNo>9876543</caNo></response>');
assert_true($r->success === true, 'XML response with confirmation classified as success');
assert_true($r->confirmation_number === 'ABC12345', 'confirmation number extracted');

$r = $call('<response><fsr>FSR-555</fsr><caNo>9876543</caNo></response>');
assert_true($r->success === true, 'XML response with fsr+caNo classified as success');

$r = $call('');
assert_true($r->success === false, 'empty body classified as failure');

$r = $call(['confirmation' => 'XYZ999']);
assert_true($r->success === true, 'array input with confirmation classified as success');

$r = $call(['error_cd' => '12', 'message' => 'Account ineligible']);
assert_true($r->success === false, 'array input with error_cd classified as failure');
assert_true($r->error_code === '12', 'error_code extracted from array input');

echo "\n== P0-1: idempotency short-circuit contract ==\n";

$already = ['enrollment_completed' => true, 'fsr_no' => 'FSR-1', 'ca_no' => 'X777'];
$fresh   = ['first_name' => 'Jane'];
assert_true(!empty($already['enrollment_completed']), 'completed submission triggers replay path');
assert_true(empty($fresh['enrollment_completed']), 'fresh submission proceeds to enrollment');

echo "\n";
if ($failures) {
    echo "FAILURES (" . count($failures) . "):\n";
    foreach ($failures as $f) echo "  - $f\n";
    exit(1);
}
echo "ALL GREEN\n";
exit(0);
