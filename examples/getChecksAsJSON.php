<?php
/**
 * Returns a list of all checks on an account as a JSON string
 * 
 * @package php-pingdom
 * @subpackage examples
 */

DEFINE('PINGDOM_USR', 'username@email.com');
DEFINE('PINGDOM_PWD', 'MyReallyStrongPassword');

require_once('../Pingdom/API.php');

$api = new Pingdom_API(PINGDOM_USR, PINGDOM_PWD);
try {
    $resp = $api->getChecks();
    echo json_encode($resp);
} catch (Exception $e) {
    echo "{ error: \"" . $e->getMessage() . "}";
}
?>
