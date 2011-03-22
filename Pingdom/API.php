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
	
}