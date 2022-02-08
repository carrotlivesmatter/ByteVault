<?php
require_once("config.php");

class bytes {
    private $client_id = client_id;
    private $secret_key = secret_key;
    private $access_token;
    private $uid;
    private $errors = [];
    private $state;
    private $authorize_url = "https://hackforums.net/api/v2/authorize";
    private $read_url = "https://hackforums.net/api/v2/read";
    private $write_url = "https://hackforums.net/api/v2/write";

    function login() {
        $this->changeState();
        $this->startAuth();
    }

    function getAccessToken() {
        return $this->access_token;
    }

    function setAccessToken($access_token) {
        $this->access_token = preg_replace("/[^A-Za-z0-9]/", "", $access_token);
    }

    function checkAccessToken() {
        if(!$this->access_token) {
            $this->setError('ACCESS_TOKEN_NOT_SET');
            return false;
        }

        return true;
    }

    function getUID() {
        return $this->uid;
    }

    function checkToken()
    {
        $read = $this->read([
            "me" => [
                "uid" => true,
                "username" => true,
                "usergroup" => true,
                "bytes" => true,
            ]
        ]);

        if($read){
            return true;
        } else {
            return false;
        }
    }

    function setError($error) {
        $this->errors[] = $error;
    }

    function getErrors() {
        return $this->errors;
    }

    function changeState() {
        $this->state = substr(str_shuffle(MD5(microtime())), 0, 12);
        return $this->state;
    }

    function setState($state) {
        $this->state = preg_replace("/[^A-Za-z0-9]/", "", $state);
    }

    function getState() {
        return $this->state;
    }

    public function randomString($length = 15)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    function sendCurl($url, $post_fields, $http_headers=[]) {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');

        if($http_headers) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headers);
        }

        $response = curl_exec($ch);

        curl_close($ch);

        return $response;
    }

    function startAuth() {
        if(!$this->client_id) {
            $this->setError('CLIENT_ID_NOT_SET');
            return false;
        }
        header("Location: {$this->authorize_url}?response_type=code&client_id={$this->client_id}&state={$this->state}");
        exit;
    }

    function finishAuth($state="") {
        $state = preg_replace("/[^A-Za-z0-9]/", "", $state);

        $input = array_change_key_case($_GET, CASE_LOWER);

        $code = preg_replace("/[^A-Za-z0-9]/", "", $input['code']);

        if($state && trim($input['state']) != trim($state)) {
            $this->setError('INVALID_STATE');
            return false;
        }

        if(!$code) {
            $this->setError('INVALID_CODE');
            return false;
        }

        if(!$this->client_id) {
            $this->setError('CLIENT_ID_NOT_SET');
            return false;
        }

        if(!$this->secret_key) {
            $this->setError('SECRET_KEY_NOT_SET');
            return false;
        }

        $response = $this->sendCurl($this->authorize_url, [
            'grant_type' => "authorization_code",
            'client_id' => $this->client_id,
            'client_secret' => $this->secret_key,
            'code' => $code
        ]);

        print_r($response);

        if(empty($response)) {
            $this->setError('BAD_RESPONSE_FROM_HF_OR_CURL_ERROR');
            return false;
        }

        try {
            $response = json_decode($response, true);
        } catch(Exception $e) {
            $this->setError('BAD_RESPONSE_FROM_HF');
            return false;
        }

        if(array_key_exists("success", $response) && $response['success'] == false) {
            if(array_key_exists("message", $response)) {
                $this->setError($response['message']);
            } else {
                $this->setError('BAD_RESPONSE_FROM_HF');
            }
            return false;
        }

        if(!array_key_exists("access_token", $response)) {
            $this->setError('BAD_RESPONSE_FROM_HF');
            return false;
        }

        $this->access_token = $response['access_token'];
        $this->uid = $response['uid'];

        return true;
    }

    function read($asks) {
        if(!$this->checkAccessToken()) {
            return;
        }

        if(!$asks) {
            $this->setError('NO_DATA_REQUESTED');
            return false;
        }

        $response = $this->sendCurl($this->read_url, [
            'asks' => json_encode($asks)
        ], ["Authorization: Bearer {$this->access_token}"]);

        if(empty($response)) {
            $this->setError('BAD_RESPONSE_FROM_HF_OR_CURL_ERROR');
            return false;
        }

        try {
            $response = json_decode($response, true);
        } catch(Exception $e) {
            $this->setError('BAD_RESPONSE_FROM_HF');
            return false;
        }

        if(array_key_exists("success", $response) && $response['success'] == false) {
            if(array_key_exists("message", $response)) {
                $this->setError($response['message']);
            } else {
                $this->setError('BAD_RESPONSE_FROM_HF');
            }
            return false;
        }

        return $response;
    }

    function write($asks) {
        if(!$this->checkAccessToken()) {
            return;
        }

        if(!$asks) {
            $this->setError('NO_DATA_REQUESTED');
            return false;
        }

        $response = $this->sendCurl($this->write_url, [
            'asks' => json_encode($asks)
        ], ["Authorization: Bearer {$this->access_token}"]);

        if(empty($response)) {
            $this->setError('BAD_RESPONSE_FROM_HF_OR_CURL_ERROR');
            return false;
        }

        try {
            $response = json_decode($response, true);
        } catch(Exception $e) {
            $this->setError('BAD_RESPONSE_FROM_HF');
            return false;
        }

        if(!is_array($response)) {
            $response = [];
        }

        if(array_key_exists("success", $response) && $response['success'] == false) {
            if(array_key_exists("message", $response)) {
                $this->setError($response['message']);
            } else {
                $this->setError('BAD_RESPONSE_FROM_HF');
            }
            return false;
        }

        return $response;
    }

    function vaultBalance() {
        if(!$this->checkAccessToken()) {
            return;
        }

        $read_vault = $this->read([
            "me" => [
                "vault" => true
            ]
        ]);

        if(!is_array($read_vault) || !array_key_exists('me', $read_vault) || !is_array($read_vault['me']) || !array_key_exists('vault', $read_vault['me'])) {
            return 0;
        }

        return (int)$read_vault['me']['vault'];
    }

    function vaultDeposit($amount) {
        if(!$this->checkAccessToken()) {
            return;
        }

        $amount = (int)$amount;
        if($amount <= 0) {
            $this->setError('NO_AMOUNT_SET');
            return false;
        }

        return $this->write([
            "bytes" => [
                "_deposit" => $amount
            ]
        ]);
    }

    function vaultWithdraw($amount) {
        if(!$this->checkAccessToken()) {
            return;
        }

        $amount = (int)$amount;
        if($amount <= 0) {
            $this->setError('NO_AMOUNT_SET');
            return false;
        }

        return $this->write([
            "bytes" => [
                "_withdraw" => $amount
            ]
        ]);
    }
}
?>