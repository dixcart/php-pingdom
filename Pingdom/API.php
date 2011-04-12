<?php

/**
  * Pingdom API PHP binding
  * (c) 2011 Dixcart Technical Solutions Limited
  *
  * THIS SOFTWARE IS PROVIDED "AS IS'' AND ANY EXPRESS OR IMPLIED WARRANTIES,
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
  
class Pingdom_API {
	
    const METHOD_GET = 1;
    const METHOD_POST = 2;
    const METHOD_DELETE = 3;
    const METHOD_PUT = 4;	
	
	const APP_KEY = "rbh7purydqixtjn7ngpyxrjwjr4sqnna";
	const API_ADDR = "https://api.pingdom.com/api/2.0";
	
	private $_apiUser;
	private $_apiPass;
	private $_acceptGzip;
	private $_lastResponse;
	private $_lastStatus;
	
	function __construct($apiUser, $apiPass, $acceptGzip = true)
	{
		if(!$apiUser || !$apiPass) {
			throw new Exception('Username/Password required');
		}
		
		$this->_apiUser = $apiUser;
		$this->_apiPass = $apiPass;
		$this->_acceptGzip = $acceptGzip;
	}
	
	function _doRequest($path, $data = null, $method = null)
	{
		$return = "";
		$curl = curl_init();
		
		//Set up curl and authentication
		curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($curl, CURLOPT_USERPWD, $this->_apiUser.":".$this->_apiPass);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_HEADER, 0);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

		
		//Set app-key header
		$headers = array(
			sprintf("%s: %s", "App-Key", self::APP_KEY)
		);
		
		//Enable GZip encoding
		if ($this->_acceptGzip) curl_setopt($curl, CURLOPT_ENCODING, "gzip");
		
		//Set URL
		curl_setopt($curl, CURLOPT_URL, self::API_ADDR.$path);
		
		
		switch($method) {
			case self::METHOD_POST:
				curl_setopt($curl, CURLOPT_POST, true);
				curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
				break;
			case self::METHOD_DELETE:
				curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
				break;
			case self::METHOD_PUT:
				curl_setopt($curl, CURLOPT_PUT, true);
				$putData = tmpfile();
				fwrite($putData, $data);
				fseek($putData, 0);
				curl_setopt($curl, CURLOPT_INFILE, $putData);
				curl_setopt($curl, CURLOPT_INFILESIZE, strlen($data));
				break;
			default:
				break;
		}
		
		//Set headers
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		
		//Send the request
		$this->_lastResponse = curl_exec($curl);
		
		//If it was a PUT we need to close the tmpfile
		if ($method == self::METHOD_PUT) fclose($putData);
		
		//Handle response and status codes
		$curlinfo = curl_getinfo($curl);
		if (!empty($curlinfo["http_code"])) {
			$this->_lastStatus = $curlinfo["http_code"];
			switch ($this->_lastStatus) {
				case '200':
					//All ok
					$return = json_decode($this->_lastResponse, true);
					break;
				case '404':
					throw new Exception('URL not found');
					break;
				case '500':
					throw new Exception('Remote server reported an Internal Server Error');
					break;
				default:
					//There was an error
					$err = json_decode($this->_lastResponse, true);
					throw new Exception('There was an error: ' . $err['error']['statuscode'] . " - " . $err['error']['satusdesc'] . ": " . $err['error']['errormessage']);
					break;
			}
		}
		
		//Clean up
		curl_close($curl);
		return $return;
	}
	
	/**
         * Returns a list of actions(alerts) that have been generated for your account
         * 
         * @param int $limit number of results to return (default=100, max = 300)
         * @param int $offset offset for listing (requires limit to be set)
         * @param int $from Unix timestamp to count checks from
         * @param int $to Unix timestamp to count checks up to
         * @param string $checkIds comma separated list of check IDs
         * @param string $contactIds comma separated list of contact IDs
         * @param string $status comma separated list of statuses
         * @param string $via comma separated list of methods alerts were sent to
         */
        public function getActions($limit = 100, $offset = 0, $from = null, $to = null, $checkIds = null, $contactIds = null, $status = null, $via = null)
        {
            if ($offset > 25000) throw new Exception('Limit set too high');

            $url = "/actions/?limit=" . $limit . "&offset=" . $offset;
            if ($from != null) $usr += "&from=" . $from;
            if ($to != null) $usr += "&to=" . $to;
            if ($checkIds != null) $usr += "&checkids=" . $checkIds;
            if ($contactIds != null) $usr += "&contactids=" . $contactIds;
            if ($status != null) $usr += "&status=" . $status;
            if ($via != null) $usr += "&via=" . $via;

            return $this->_doRequest($url);
        }
        
        /**
         * Returns a list of the latest error analysis results for a specified check
         * 
         * @param int $checkId The check ID to get analysis for
         * @param int $limit number of results to return (default = 100)
         * @param int $offset offset for listing
         */
        public function getError($checkId, $limit = 100, $offset = 0)
        {
            $url = "/analysis/" . $checkId . "/?limit=" . $limit . "&offset=" . $offset;
            return $this->_doRequest($url);
        }
        
        /**
         * Returns the raw result for a specified error analysis. This data is primarily intended for internal
         * use, but you might be interested in it as well. However, there is no real documentation for this
         * data at the moment. In the future, we may add a new API method that provides a more user-friendly format.
         * 
         * @param int $checkId ID of the check
         * @param int $analysisId ID of the analysis
         */
        public function getRawAnalysis($checkId, $analysisId)
        {
            $url = "/analysis/" . $checkId . "/" . $analysisId;
            return $this->_doRequest($url);
        }
        
        /**
        * Gets a list of all checks on the account
        *
        * @param int $limit number of results to return (default=500, max = 25000)
        * @param int $offset offset for listing (requires limit to be set)
        * @return array JSON response encoded to array
        */
        public function getChecks($limit = 500, $offset = 0)
        {
            if ($offset > 25000) throw new Exception('Limit set too high');

            $url = "/checks/?limit=" . $limit . "&offset=" . $offset;
            return $this->_doRequest($url);
        }
        
        /**
         * Returns a detailed description of a specified check
         * 
         * @param int $checkId the ID of the check you wish to get more detail on (see getChecks)
         * @return array JSON response encoded to array
         */
        public function getCheck($checkId)
        {
            $url = "/checks/" . $checkId;
            return $this->_doRequest($url);
            
        }
        
        /**
         * Internal function to handle adding all types of check, assumes other functions
         * have validated the data
         * 
         * @param Pingdom_Check $check Check object to insert
         * @return string JSON string of result
         */
        public function _addCheck($check)
        {
            $url = "/checks";
            $postData = $check->_prepData();
            return $this->_doRequest($url, $postData, self::METHOD_POST);
        }
	
}

/**
 * Parent Check class
 * 
 * 
 */
class Pingdom_Check
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
        
        $post += "name=" . $this->name;
        $post += "&host=" . $this->host;
        $post += "&type=" . $this->type;
        $post += "&paused=" . $this->paused;
        $post += "&resolution=" . $this->resolution;
        if ($this->contactIds != null)
        {
            $post += "&contactids=" . $this->contactIds;
            $post += "&sendtoemail=" . $this->sendToEmail;
            $post += "&sendtosms=" . $this->sendToSms;
            $post += "&sendtotwitter=" . $this->sendToSms;
            $post += "&sendtoiphone=" . $this->sendToIphone;
            $post += "&sendnotificationwhendown=" . $this->notifyWhenDown;
            $post += "&notifyagainevery=" . $this->notifyAgainEvery;
            $post += "&notifywhenbackup=" . $this->notifyWhenBack;
        }
        
        return $post;
    }
}

class HTTP_Check extends Pingdom_Check
{
    public $url = "/";
    public $encryption = false;
    public $port = 80;
    public $auth = null;
    public $shouldContain = null;
    public $shouldNotContain = null;
    public $postData = null;
    public $requestHeaders = array();
    
    function __construct($name, $host) {
        parent::__construct($name, $host, "http");
    }
    
    function _prepData() {
        $post = parent::_prepData();
        
        $post += "&url=" . $this->url;
        $post += "&encryption=" . $this->encryption;
        $post += "&port=" . $this->port;
        if ($this->auth != null) $post += "&auth=" . $this->auth;
        if ($this->shouldContain != null) $post += "&shouldcontain=" . $this->shouldContain;
        if ($this->shouldNotContain != null) $post += "&shouldnotcontain=" . $this->shouldNotContain;
        //if ($this->postData != null) $post += "&postdata=" . $this->postData;  //TODO: Find out how this should actually be formatted, how do you POST POST data?
        
        
        return $post;
    }
    
}

class HTTP_Custom_Check extends Pingdom_Check
{
    public $url = "/";
    public $encryption = false;
    public $port = 80;
    public $auth = null;
    public $additionalUrls = array();
    
    function __construct($name, $host, $url) {
        parent::__construct($name, $host, "httpcustom");
        $this->url = $url;
    }
 
}

class TCP_Check extends Pingdom_Check
{
    public $port;
    public $stringToSend = null;
    public $stringToExpect = null;
    
    function __construct($name, $host, $port) {
        parent::__construct($name, $host, "tcp");
        $this->port = $port;
    }
    
}

class Ping_Check extends Pingdom_Check
{
    function __construct($name, $host) {
        parent::__construct($name, $host, "ping");
    }
}

class DNS_Check extends Pingdom_Check
{
    public $expectedIp;
    public $nameServer;
    
    function __construct($name, $host, $nameServer, $expectedIp) {
        parent::__construct($name, $host, "dns");
        $this->nameServer = $nameServer;
        $this->expectedIp = $expectedIp;
    }
}

class UDP_Check extends Pingdom_Check
{
    public $port;
    public $stringToSend;
    public $stringToExpect;
    
    function __construct($name, $host, $port, $stringToSend, $stringToExpect) {
        parent::__construct($name, $host, "udp");
        $this->port = $port;
        $this->stringToSend = $stringToSend;
        $this->stringToExpect = $stringToExpect;
    }
    
}

class SMTP_Check extends Pingdom_Check
{
    public $port = 25;
    public $auth = null;
    public $stringToExpect = null;
    public $encryption = false;
    
    function __construct($name, $host) {
        parent::__construct($name, $host, "smtp");
    }
 
}

class POP3_Check extends Pingdom_Check
{
    public $port = 110;
    public $stringToExpect = null;
    public $encryption = false;
    
    function __construct($name, $host) {
        parent::__construct($name, $host, "pop3");
    }
  
}

class IMAP_Check extends Pingdom_Check
{
    public $port = 143;
    public $stringToExpect = null;
    public $encryption = false;
    
    function __construct($name, $host) {
        parent::__construct($name, $host, "imap");
    }

}