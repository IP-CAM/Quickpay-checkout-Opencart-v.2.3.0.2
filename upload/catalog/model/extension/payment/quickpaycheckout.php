<?php

class QuickPayException extends Exception {
    
}

class QuickPay {

    protected $_username;
    protected $_apikey;
    protected $_requestBody;
    protected $_requestURI;
    protected $_responseBody;
    protected $_responseInfo;
    protected $_Headers;
    protected $HTTP_CODE_OK;
    protected $_PAY_URL;

    //protected $_PAY_URL;
    const _SUCCESS_RCODE = 200;

    /**
     *  Initialises the class
     * @param type $apiKey
     * @throws QuickPayException
     */
    public function QuickPay($apiKey, $url) {
        $this->_PAY_URL = $url;//"https://checkout-test.quickpay.co.ke/chargetoken";
        if (strlen($apiKey) == 0) {
            throw new QuickPayException('Please supply both username and apikey files. ');
        } else {
            $this->_apikey = $apiKey;
        }
    }

    /**
     *
     * @param type $referenceNo
     * @param type $orderInfo
     * @param type $amount
     * @param type $token
     * @param type $Currency
     */
    public function sendMessage($referenceNo, $orderInfo, $amount, $token, $Currency) {
        if (empty($referenceNo) || empty($orderInfo) || empty($amount) || empty($token) || empty($Currency)) {
            throw new QuickPayException('Please supply both username and apikey files. ');
        } else {
            $params = array(
                "reference" => $referenceNo, "orderinfo" => $orderInfo,
                "currency" => $Currency, "amount" => $amount,
                "userkey" => $this->_apikey, "token" => $token
            );
//            Set up channel configurations
            $this->HTTP_CODE_OK = self::_SUCCESS_RCODE;
            $this->_requestURI = $this->_PAY_URL;//self::_PAY_URL;
            $this->_requestBody = json_encode($params);
            $this->exeutePost($params);
            if ($this->_responseInfo['http_code'] == self::_SUCCESS_RCODE) {
                $responseObject = json_decode($this->_responseBody);
                return $responseObject;
            } else {
                throw new QuickPayException($this->_responseBody);
            }
            return "Error";
        }
    }

    private function exeutePost($params) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->_requestBody);
        curl_setopt($ch, CURLOPT_POST, 1);
        $_Headers = array();
        $_Headers[] = 'SecureHash: ' . base64_encode(hash_hmac("sha256", json_encode($params), $this->_apikey, true));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $_Headers);
        $this->doExecute($ch);
    }

    /**
     *
     * @param type $curlHandle_
     * @throws Exeption
     */
    private function doExecute(&$curlHandle_) {
        try {
            $this->setCurlOpts($curlHandle_);
            $responseBody = curl_exec($curlHandle_);
            $this->_responseInfo = curl_getinfo($curlHandle_);
            $this->_responseBody = $responseBody;
            curl_close($curlHandle_);
        } catch (Exeption $e) {
            curl_close($curlHandle_);
            throw $e;
        }
    }

    /**
     * 
     * @param type $curlHandle_s
     */
    private function setCurlOpts(&$curlHandle_) {
        curl_setopt($curlHandle_, CURLOPT_TIMEOUT, 60);
        curl_setopt($curlHandle_, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curlHandle_, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curlHandle_, CURLOPT_URL, $this->_requestURI);
        curl_setopt($curlHandle_, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlHandle_, CURLOPT_TIMEOUT, 15);
        curl_setopt($curlHandle_, CURLOPT_CONNECTTIMEOUT, 15);
        curl_setopt($curlHandle_, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    }

}

class ModelExtensionPaymentQuickpaycheckout extends Model {

    public $error_msg = '';
    public $status_code = 0;
    private $table_name;
    private $table_order;
    private $order_primary_key = "order_id";
    private $primary_key = "id";
    private $order_id = "order_id";
    private $trans_id = "transaction_id";
    private $auth_id = "authentication_id";
    private $ref_no = "reference_no";
    private $token = "request_token";
    private $receipt_no = "receipt_no";
    private $currency = "currency";
    private $amount = "amount";
    private $order_info = "order_info";
    private $response_code = "response_code";
    private $created_at = "created_at";
    private $merchant_id = "merchant_id";
    private $description = "description";
    
    public function __construct($param) {
        parent::__construct($param);
        $this->table_name = DB_PREFIX."quickpaycheckout_transactions";
    }

    public function getMethod($address, $total) {
        //validate currency
        $supported_currency = array("USD", "KES");
        $current_currency = strtoupper(isset($this->session->data['currency']) ? $this->session->data['currency'] : '');
        if(!in_array($current_currency, $supported_currency)){
            return array();
        }
        $this->load->language('extension/payment/quickpaycheckout');
        $base_url = $this->config->get('config_url').'/image/catalog/quickpay_checkout/payment/';
        $method_data = array(
            'code' => 'quickpaycheckout',
            'title' => '<img src="'.$base_url.'/quickpay.png" title="'.$this->language->get('quickpay_title').'" alt="'.$this->language->get('quickpay_alt').'" />'
            . '<img src="'.$base_url.'/visa.png" title="'.$this->language->get('visa_title').'" alt="'.$this->language->get('visa_alt').'" />'
            . '<img src="'.$base_url.'/unionpay.png" title="'.$this->language->get('unionpay_title').'" alt="'.$this->language->get('unionpay_alt').'" />'
            . '<img src="'.$base_url.'/mastercard.png" title="'.$this->language->get('mastercard_title').'" alt="'.$this->language->get('mastercard_alt').'" />'
            ,
            'terms' => '<a href="https://quickpay.co.ke/terms/" target="_blank">Terms & Conditions</a>',
            'sort_order' => $this->config->get("quickpaycheckout_sort_order")
        );
        return $method_data;
    }

    /**
     * Used to perform quickpay server processing checkout
     * @param type $apikey quickpay private key
     * @param type $referenceNo unique reference number
     * @param type $orderInfo order description
     * @param type $amount amount of order
     * @param type $token public token
     * @param type $Currency ISO currency code
     * @return boolean returns true on success and array with status code and message on failure
     */
    public function checkout($apikey, $referenceNo, $orderInfo, $amount, $token, $Currency) {
        try {
            $url = "https://checkout".(($this->config->get('quickpaycheckout_environment') == 0) ? '-test' : '').".quickpay.co.ke/chargetoken";
            $gateway = new QuickPay($apikey, $url);
            //Perform payment
            $response = $gateway->sendMessage($referenceNo, $orderInfo, $amount, $token, $Currency);           
            return $response;
        } catch (Exception $ex) {
            $this->log->write("----Quickpay Checkout Error----\n" . $ex->getMessage());
            return array("failed" => true, "message" => $ex->getMessage());
        }
    }

    /**
     * Used to insert new quickpay transaction response
     * @param type $data transaction data
     */
    public function insertTransaction($data) {
        $this->db->query("insert into ".$this->table_name."(".$this->amount.", ".$this->auth_id.", ".$this->currency.", "
                . $this->order_id .", ".$this->order_info.", ".$this->trans_id.", ".$this->token.", ".$this->response_code.","
                . " ".$this->ref_no.",".$this->receipt_no.", ".$this->merchant_id.", ".$this->description.")"
                . "values('".$this->db->escape($data['amount'])."', '".$this->db->escape($data['auth_id'])."', '".$this->db->escape($data['currency'])."',"
                . " '".$this->db->escape($data['order_id'])."', '".$this->db->escape($data['order_info'])."'"
                . ", '".$this->db->escape($data['trans_id'])."', '".$this->db->escape($data['token'])."', "
                . "'".$this->db->escape($data['response_code'])."', '".$this->db->escape($data['ref_no'])."'"
                . ", '".$this->db->escape($data['receipt_no'])."', '".$this->db->escape($data['merchant_id'])."', '".$this->db->escape($data['description'])."')");
    }

}
