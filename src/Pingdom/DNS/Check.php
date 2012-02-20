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

class Pingdom_DNS_Check extends Pingdom_Check
{
    public $expectedIp;
    public $nameServer;

    function __construct($name, $host, $nameServer, $expectedIp) {
        parent::__construct($name, $host, "dns");
        $this->nameServer = $nameServer;
        $this->expectedIp = $expectedIp;
    }
}
