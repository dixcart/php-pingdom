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
namespace Pingdom;

/**
 * Parent Check class
 */
class Pingdom
{
    // Required variables
    public $name;
    public $host;
    public $type;

    //optional vars
    public $paused = false;
    public $resolution = 5;
    public $contactIds = null;
    public $sendToEmail = false;
    public $sendToSms = false;
    public $sendToTwitter = false;
    public $sendToIphone = false;
    public $notifyWhenDown = 2;
    public $notifyAgainEvery = 0;
    public $notifyWhenBack = true;

    /**
     * Constructor uses the bare minimum options for a check, this is all you need
     * for a check, but you can go into more detail if required
     *
     * @param string $name The friendly name for the check
     * @param string $host DNS or IP for the check to use
     * @param string $type one of http, httpcustom, tcp, ping, dns, udp, smtp, pop3, imap (usually specified by more specific class)
     *
     */
    function __construct($name, $host, $type)
    {
        $this->name = $name;
        $this->host = $host;
        $this->type = $type;
    }

    function _prepData()
    {
        $post = "";

        $post .= "name=" . $this->name;
        $post .= "&host=" . $this->host;
        $post .= "&type=" . $this->type;
        $post .= "&paused=" . $this->paused;
        $post .= "&resolution=" . $this->resolution;
        if ($this->contactIds != null)
        {
            $post .= "&contactids=" . $this->contactIds;
            $post .= "&sendtoemail=" . $this->sendToEmail;
            $post .= "&sendtosms=" . $this->sendToSms;
            $post .= "&sendtotwitter=" . $this->sendToSms;
            $post .= "&sendtoiphone=" . $this->sendToIphone;
            $post .= "&sendnotificationwhendown=" . $this->notifyWhenDown;
            $post .= "&notifyagainevery=" . $this->notifyAgainEvery;
            $post .= "&notifywhenbackup=" . $this->notifyWhenBack;
        }

        return $post;
    }
}
