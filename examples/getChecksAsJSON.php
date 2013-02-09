<?php

/**
 * Pingdom API PHP binding
 * (c) 2011 Dixcart Technical Solutions Limited
 *
 * THIS SOFTWARE IS PROVIDED "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES,
 * INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY
 * AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
 *
 * IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO,
 * PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS;
 * OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY,
 * WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE
 * OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE,
 * EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @author Richard Benson <richard.benson@dixcart.com>
 * @link http://github.com/dixcart/php-pingdom
 * @link http://dixcart.com/it
 * @version 1
 * @license bsd
 */
/**
 * Returns a list of all checks on an account as a JSON string
 *
 * @package php-pingdom
 * @subpackage examples
 */

DEFINE('PINGDOM_USR',    'username@email.com');
DEFINE('PINGDOM_PWD',    'MyReallyStrongPassword');
DEFINE('PINGDOM_APIKEY', 'myapikey');

require_once dirname(__FILE__).'/../src/Pingdom/Autoload.php';
Pingdom_Autoload::register();

$api = new Pingdom_API(PINGDOM_USR, PINGDOM_PWD, PINGDOM_APIKEY);
try {
    $resp = $api->getChecks();
    echo json_encode($resp);
} catch (Exception $e) {
    echo "{ error: \"" . $e->getMessage() . "}";
}
?>
