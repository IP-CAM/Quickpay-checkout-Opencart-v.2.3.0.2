<?php

class ControllerExtensionPaymentQuickpaycheckout extends Controller
{

    public function __construct($param)
    {
        parent::__construct($param);
        $this->load->model('checkout/order');
    }

    public function index()
    {
        if (!isset($this->session->data['order_id']))
        {
            return;
        }
        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        $data['button_confirm'] = $this->language->get('button_confirm');
        $data['text_loading'] = $this->language->get('text_loading');
        $data['continue'] = $this->url->link('checkout/success');
        $data['action'] = $this->url->link('extension/payment/quickpaycheckout/submit', 'token=' . (isset($this->session->data['token']) ? $this->session->data['token'] : '' ), 'SSL');
        $data['req_key'] = $this->config->get("quickpaycheckout_public_key");
        $data['req_store'] = $this->config->get("quickpaycheckout_merchant_name");
        $data['req_desc'] = $this->config->get("quickpaycheckout_merchant_desc");
        $data['req_button_text'] = $this->config->get("quickpaycheckout_payment_button");
        $total = $this->calculateTotalCurrency($order_info['total'], $order_info['currency_value']);
        $data['req_amount'] = $total;
        $data['js_request_url'] = $url = "https://checkout".(($this->config->get('quickpaycheckout_environment') == 0) ? '-test' : '').".quickpay.co.ke/js";            
        $data['req_image'] = $this->config->get('config_url') . $this->config->get("quickpaycheckout_payment_icon");
        $data['req_currency'] = (isset($order_info['currency_code'])) ? $order_info['currency_code'] : 'KES';
        $data['redirect_url'] = $this->url->link('payment/quickpaycheckout/preview');
        return $this->load->view('extension/payment/quickpaycheckout.tpl', $data);
    }

    private function calculateTotalCurrency($amount, $currency_value)
    {
        return round(($amount * $currency_value), 2);
    }

    /**
     * used to package response from an object to an array
     * @param type $result
     */
    private function packageResponse($order_id, $result, $token)
    {
        $data['amount'] = isset($result->data->data->amount) ? $result->data->data->amount : '';
        $data['currency'] = isset($result->data->data->currency) ? $result->data->data->currency : '';
        $data['merchant_id'] = isset($result->data->data->merchantID) ? $result->data->data->merchantID : '';
        $data['ref_no'] = isset($result->data->data->referenceNo) ? $result->data->data->referenceNo : '';
        $data['order_info'] = isset($result->data->data->orderInfo) ? $result->data->data->orderInfo : '';
        $data['receipt_no'] = isset($result->data->data->receiptNo) ? $result->data->data->receiptNo : '';
        $data['auth_id'] = isset($result->data->data->authId) ? $result->data->data->authId : '';
        $data['response_code'] = isset($result->data->data->responseCode) ? $result->data->data->responseCode : '';
        $data['trans_id'] = isset($result->data->data->transactionNo) ? $result->data->data->transactionNo : '';
        $data['description'] = isset($result->data->message) ? $result->data->message : '';
        $data['status_code'] = isset($result->code) ? $result->code : '';
        $data['order_id'] = $order_id;
        $data['token'] = $token;
        //format amount
        $data['amount'] = (double)($data['amount']/100);
        return $data;
    }

    /**
     * Used to perform server processing (Sends request to quickpay servers and records the response accordingly)
     */
    public function submit()
    {
        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        if (($this->request->server['REQUEST_METHOD'] == 'POST'))
        {
            $apikey = $this->config->get("quickpaycheckout_private_key");
            //package request parameters
            $referenceNo = $order_info['invoice_prefix'] . $order_info['invoice_no'] . '-' . time();
            $orderInfo = $order_info['order_id'] . '-' . time();
            $amount = (string) $this->calculateTotalCurrency($order_info['total'], $order_info['currency_value']);  //Use the same amount that was used to get the token
            $token = $this->request->post['qpToken']; //Token received from QuickPay
            $Currency = $order_info['currency_code'];

            $this->load->model('extension/payment/quickpaycheckout');
            //send request 
            $result = $this->model_extension_payment_quickpaycheckout->checkout($apikey, $referenceNo, $orderInfo, $amount, $token, $Currency);

            //Validate response
            if (is_array($result))
            {//If an exception occurs record and redirect to failure page
                $this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $this->config->get('quickpaycheckout_failed_status'), $result["message"]);
                $this->response->redirect($this->url->link('checkout/failure'));
            }
            $status_code = (int) $result->code;
            //package response into an array
            $data = $this->packageResponse($order_info['order_id'], $result, $token);
            if ($status_code === 0)
            {//if the request is successfully record and redirect to the success page
                $this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $this->config->get('quickpaycheckout_order_status'));
                //insert transaction response in the database
                $this->model_extension_payment_quickpaycheckout->insertTransaction($data);
                $this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $this->config->get('quickpaycheckout_success_status'));
                $this->response->redirect($this->url->link('checkout/success'));
            }

            if ($status_code > 1 && $status_code < 10)
            { //if the bank declined the request record               
                $this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $this->config->get('quickpaycheckout_declined_status'));
            } else
            {//if other errors occured record the response
                $this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $this->config->get('quickpaycheckout_failed_status'));
            }
            //insert transaction response in the database and log error then redirect to the failure page
            $this->log->write("---------Quckpay Checkout Error--------------\n" . json_encode($result));
            $this->model_extension_payment_quickpaycheckout->insertTransaction($data);
            $this->response->redirect($this->url->link('checkout/failure'));
        }
    }

    function clean()
    {
        $file1 = 'catalog/model/payment/quickpaycheckout.php';
        $file2 = 'catalog/controller/payment/quickpaycheckout.php';
        $file3 = 'catalog/language/english/payment/quickpaycheckout.php';
        $file4 = 'admin/model/payment/quickpaycheckout.php';
        $file5 = 'admin/controller/payment/quickpaycheckout.php';
        $file6 = 'admin/language/english/payment/quickpaycheckout.php';


        $this->delete($file1);
        $this->delete($file2);
        $this->delete($file3);
        $this->delete($file4);
        $this->delete($file5);
        $this->delete($file6);
//        if (file_exists('catalog/controller/payment/test.txt'))
//        {
//            echo 'File Exists!!!!!!!!!!!!';
//        }
//        if(file_exists('/admin/language/english/payment/quickpaycheckout.php')){
//            echo 'file exists';
//        }else{
//            echo 'Does not exit';
//        }
    }

    private function delete($file)
    {
        if (file_exists($file))
        {
            echo "<br />-----------------File " . $file . " Proceed with cleaning...";

            unlink($file);
        } else
        {
            echo"<br />-------------------File Doesn't Exist: " . $file;
        }
    }

}
