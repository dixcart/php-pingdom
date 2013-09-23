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

class Api
{

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
        if (!$apiUser || !$apiPass) {
            throw new \Exception('Username/Password required');
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
        curl_setopt($curl, CURLOPT_USERPWD, $this->_apiUser . ":" . $this->_apiPass);
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
        curl_setopt($curl, CURLOPT_URL, self::API_ADDR . $path);

        $putData = '';
        switch ($method) {
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
        if ($method == self::METHOD_PUT) {
            fclose($putData);
        }

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
                    throw new \Exception('URL not found');
                    break;
                case '500':
                    throw new \Exception('Remote server reported an Internal Server Error');
                    break;
                default:
                    //There was an error
                    $err = json_decode($this->_lastResponse, true);
                    throw new \Exception('There was an error: ' . $err['error']['statuscode'] . " - " . $err['error']['statusdesc'] . ": " . $err['error']['errormessage']);
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
     * @param int $limit : number of results to return (default=100, max = 300)
     * @param int $offset : offset for listing (requires limit to be set)
     * @param null|int $from : Unix timestamp to count checks from
     * @param null|int $to : Unix timestamp to count checks up to
     * @param null|string $checkIds : comma separated list of check IDs
     * @param null|string $contactIds : comma separated list of contact IDs
     * @param null|string $status : comma separated list of statuses
     * @param null|string $via : comma separated list of methods alerts were sent to
     * @return mixed|string
     * @throws \Exception
     */
    public function getActions($limit = 100, $offset = 0, $from = null, $to = null, $checkIds = null, $contactIds = null, $status = null, $via = null)
    {
        if ($offset > 25000) throw new \Exception('Limit set too high');

        $url = "/actions/?limit=" . $limit . "&offset=" . $offset;
        if ($from != null) $url .= "&from=" . $from;
        if ($to != null) $url .= "&to=" . $to;
        if ($checkIds != null) $url .= "&checkids=" . $checkIds;
        if ($contactIds != null) $url .= "&contactids=" . $contactIds;
        if ($status != null) $url .= "&status=" . $status;
        if ($via != null) $url .= "&via=" . $via;

        return $this->_doRequest($url);
    }

    /**
     * Returns a list of the latest error analysis results for a specified check
     *
     * @param int $checkId : The check ID to get analysis for
     * @param int $limit : number of results to return (default = 100)
     * @param int $offset : offset for listing
     * @return mixed|string
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
     * @param int $checkId : of the check
     * @param int $analysisId : ID of the analysis
     * @return mixed|string
     */
    public function getRawAnalysis($checkId, $analysisId)
    {
        $url = "/analysis/" . $checkId . "/" . $analysisId;
        return $this->_doRequest($url);
    }

    /**
     * Gets a list of all checks on the account
     *
     * @param int $limit : number of results to return (default=500, max = 25000)
     * @param int $offset : offset for listing (requires limit to be set)
     * @return array : JSON response encoded to array
     * @throws \Exception
     */
    public function getChecks($limit = 500, $offset = 0)
    {
        if ($offset > 25000) throw new \Exception('Limit set too high');

        $url = "/checks/?limit=" . $limit . "&offset=" . $offset;
        return $this->_doRequest($url);
    }

    public function getProbes($limit = 500, $offset = 0, $onlyactive = "false")
    {
        if ($offset > 25000) throw new \Exception('Limit set too high');
        $url = "/probes?limit=" . $limit . "&offset=" . $offset . "&onlyactive=" . $onlyactive;
        return $this->_doRequest($url);
    }

    public function getTraceroute($target, $probeid = 0)
    {
        $url = "/traceroute" . "?host=" . $target . "&probeid=" . $probeid;
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
     * @param Pingdom $check Check object to insert
     * @return string JSON string of result
     */
    public function addCheck($check)
    {
        $url = "/checks";
        $postData = $check->_prepData();
        return $this->_doRequest($url, $postData, self::METHOD_POST);
    }
}