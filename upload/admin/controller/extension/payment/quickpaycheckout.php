<?php

class ControllerExtensionPaymentQuickpaycheckout extends Controller
{

    public function __construct($param)
    {
        parent::__construct($param);
        $this->load->model('setting/setting');
        $this->load->model("extension/payment/quickpaycheckout");
        $this->language->load('extension/payment/quickpaycheckout');
        $this->document->setTitle($this->language->get('document_title'));
    }

    /**
     * Used to process uploaded image. 
     * Ensure image is of 150 * 150 dimensions, jpeg or png or gif formats.
     * On success upload image and creates new post variable with image name
     * @return Mixed Returns true on success and String on failure with error information
     */
    private function handleFileUpload()
    {
        if (is_uploaded_file($this->request->files['payment_icon']['tmp_name']) && file_exists($this->request->files['payment_icon']['tmp_name']))
        {
            //validate image
            $formats = array(
                IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_GIF
            );
            $ext = exif_imagetype($this->request->files['payment_icon']['tmp_name']);
            if (!in_array($ext, $formats))
            {
                return "Sorry invalid file format (Supported format; jpg, jpeg, gif and png)";
            }
            $image_size = getimagesize($this->request->files['payment_icon']['tmp_name']);
            if ($image_size[0] !== 150 && $image_size[1] !== 150)
            {
                return "Sorry invalid image size (Required size is 150 * 150)";
            }
            $dir = "../image/catalog/quickpay_checkout";
            if (!is_dir($dir))
            {//create directory if doesn't exist
                mkdir($dir, 0777, true);
            }
            //clear previous image
            $this->clearExistingIcon();

            $url = $dir . "/" . strtolower($this->request->files['payment_icon']['name']);
            move_uploaded_file($this->request->files['payment_icon']['tmp_name'], $url);
            //create post variable with image name            
            $this->request->post["quickpaycheckout_payment_icon"] = substr($url, 3);
            return true;
        } else
        {//return true since image icon is optional
            //retaining the previous image url
            $this->request->post["quickpaycheckout_payment_icon"] = $this->config->get("quickpaycheckout_payment_icon");
            return true;
        }
    }

    /**
     * Load configuration page parent header, sidebar, footer and breadcrumbl.
     * Load application settings 
     */
    public function index()
    {
        if (($this->request->server['REQUEST_METHOD'] == 'POST'))
        { // Start If: Validates and check if data is coming by save (POST) method
            //validate settings
            $validation_errors = $this->validate();
            if (sizeof($validation_errors) > 0)
            {
                $data['validation_errors'] = $validation_errors;
            }
            //process uploaded image
            $upload_response = $this->handleFileUpload();
            if ($upload_response !== true)
            {
                $data['validation_errors'] = array($upload_response);
            } else
            {
                $this->model_setting_setting->editSetting('quickpaycheckout', $this->request->post);
                $this->session->data['success'] = $this->language->get('text_success'); // To display the success text on data save

                $this->response->redirect($this->url->link('extension/extension', 'token=' . $this->session->data['token'], 'SSL')); // Redirect to the Module Listing
            }
        } // End If
        //creating breadcrumbs
        $data['breadcrumbs'] = array();
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL')
        );
        $this->load->language('payment/quickpaycheckout');
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_module'),
            'href' => $this->url->link('extension/extension', 'token=' . $this->session->data['token'], 'SSL')
        );

        if (!isset($this->request->get['module_id']))
        {
            $data['breadcrumbs'][] = array(
                'text' => $this->language->get('heading_title'),
                'href' => $this->url->link('extension/payment/quickpaycheckout', 'token=' . $this->session->data['token'], 'SSL')
            );
        } else
        {
            $data['breadcrumbs'][] = array(
                'text' => $this->language->get('heading_title'),
                'href' => $this->url->link('extension/payment/quickpaycheckout', 'token=' . $this->session->data['token'] . '&module_id=' . $this->request->get['module_id'], 'SSL')
            );
        }
        $data['environment_test'] = $this->language->get('environment_test');
        $data['environment_live'] = $this->language->get('environment_live');
        $data['environment_desc'] = $this->language->get('environment_desc');
        $data['environment'] = $this->language->get('environment');
        $data['heading_title'] = $this->language->get('heading_title');
        $data['text_edit'] = $this->language->get('text_edit');
        $data['button_cancel'] = $this->language->get('button_cancel');
        $data['button_submit'] = $this->language->get('button_submit');
        $data['enabled'] = $this->language->get('enabled');
        $data['disabled'] = $this->language->get('disabled');
        $data['cancel'] = $this->url->link('extension/extension', 'token=' . $this->session->data['token'], 'SSL');
        $data['submit'] = $this->url->link('extension/payment/quickpaycheckout', 'token=' . $this->session->data['token'], 'SSL');
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        $data['merchant_name'] = $this->language->get("Merchant Name");
        $data['payment_icon'] = $this->language->get('payment_icon');
        $data['payment_icon_desc'] = $this->language->get('payment_icon_desc');
        $data['merchant_name_desc'] = $this->language->get("merchant_name_desc");
        $data['public_key'] = $this->language->get("Public Key");
        $data['public_key_desc'] = $this->language->get("public_key_desc");
        $data['private_key'] = $this->language->get("Private Key");
        $data['private_key_desc'] = $this->language->get("private_key_desc");
        $data['merchant_desc'] = $this->language->get("merchant_desc");
        $data['merchant_desc_description'] = $this->language->get("merchant_desc_description");
        $data['status'] = $this->language->get("Status");
        $data['status_desc'] = $this->language->get("status_desc");
        $data['completed_status'] = $this->language->get("completed_status");
        $data['completed_status_desc'] = $this->language->get("completed_status_desc");
        $data['declined_status'] = $this->language->get("declined_status");
        $data['declined_status_desc'] = $this->language->get("declined_status_desc");
        $data['failed_status'] = $this->language->get("failed_status");
        $data['failed_status_desc'] = $this->language->get("failed_status_desc");
        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
        $data['payment_button'] = $this->language->get("payment_button");
        $data['payment_button_desc'] = $this->language->get("payment_button_desc");
        $data['sort_order'] = $this->language->get("sort_order");
        $data['sort_order_desc'] = $this->language->get("sort_order_desc");
        //Get existing settings
        //get environment settings
        if (isset($this->request->post['quickpaycheckout_environment']))
        {
            $data['quickpaycheckout_environment'] = $this->request->post['quickpaycheckout_environment'];
        } else
        {
            $data["quickpaycheckout_environment"] = $this->config->get("quickpaycheckout_environment"); //$db_response["title"];
        }

        if (isset($this->request->post['quickpaycheckout_merchant_name']))
        {
            $data['quickpaycheckout_merchant_name'] = $this->request->post['quickpaycheckout_merchant_name'];
        } else
        {
            $data["quickpaycheckout_merchant_name"] = $this->config->get("quickpaycheckout_merchant_name"); //$db_response["title"];
        }
        if (isset($this->request->post['quickpaycheckout_public_key']))
        {
            $data['quickpaycheckout_public_key'] = $this->request->post['quickpaycheckout_public_key'];
        } else
        {
            $data["quickpaycheckout_public_key"] = $this->config->get("quickpaycheckout_public_key"); ///$db_response["html_template"];
        }
        if (isset($this->request->post['quickpaycheckout_private_key']))
        {
            $data['quickpaycheckout_private_key'] = $this->request->post['quickpaycheckout_private_key'];
        } else
        {
            $data["quickpaycheckout_private_key"] = $this->config->get("quickpaycheckout_private_key"); ///$db_response["html_template"];
        }
        //check if merchant name is available and intialize it
        if (isset($this->request->post['quickpaycheckout_merchant_desc']))
        {
            $data['quickpaycheckout_merchant_desc'] = $this->request->post['quickpaycheckout_merchant_desc'];
        } else
        {
            $data["quickpaycheckout_merchant_desc"] = $this->config->get("quickpaycheckout_merchant_desc"); ///$db_response["html_template"];
        }
        //check if payment button text is available and intialize it
        if (isset($this->request->post['quickpaycheckout_payment_button']))
        {
            $data['quickpaycheckout_payment_button'] = $this->request->post['quickpaycheckout_payment_button'];
        } else
        {
            $data["quickpaycheckout_payment_button"] = $this->config->get("quickpaycheckout_payment_button"); ///$db_response["html_template"];
        }
        //check if sort order is available and intialize it
        if (isset($this->request->post['quickpaycheckout_sort_order']))
        {
            $data['quickpaycheckout_sort_order'] = $this->request->post['quickpaycheckout_sort_order'];
        } else
        {
            $data["quickpaycheckout_sort_order"] = $this->config->get("quickpaycheckout_sort_order"); ///$db_response["html_template"];
        }
        if (isset($this->request->post['quickpaycheckout_status']))
        {
            $data['quickpaycheckout_status'] = $this->request->post['quickpaycheckout_status'];
        } else
        {
            $data["quickpaycheckout_status"] = $this->config->get("quickpaycheckout_status"); ///$db_response["html_template"];
        }
        if (isset($this->request->post['quickpaycheckout_success_status']))
        {
            $data['quickpaycheckout_success_status'] = $this->request->post['quickpaycheckout_success_status'];
        } else if ($this->config->get("quickpaycheckout_success_status") == "")
        {
            $data["quickpaycheckout_success_status"] = 5;
        } else
        {
            $data["quickpaycheckout_success_status"] = $this->config->get("quickpaycheckout_success_status"); ///$db_response["html_template"];
        }
        if (isset($this->request->post['quickpaycheckout_declined_status']))
        {
            $data['quickpaycheckout_declined_status'] = $this->request->post['quickpaycheckout_declined_status'];
        } else if ($this->config->get("quickpaycheckout_declined_status") == "")
        {
            $data['quickpaycheckout_declined_status'] = 8;
        } else
        {
            $data["quickpaycheckout_declined_status"] = $this->config->get("quickpaycheckout_declined_status"); ///$db_response["html_template"];
        }
        if (isset($this->request->post['quickpaycheckout_failed_status']))
        {
            $data['quickpaycheckout_failed_status'] = $this->request->post['quickpaycheckout_failed_status'];
        } else if ($this->config->get("quickpaycheckout_failed_status") == '')
        {
            $data["quickpaycheckout_failed_status"] = 10;
        } else
        {
            //$this->log->write("------------------Status: " . $this->config->get("quickpaycheckout_failed_status"));
            $data["quickpaycheckout_failed_status"] = $this->config->get("quickpaycheckout_failed_status"); ///$db_response["html_template"];
        }


        $this->response->setOutput($this->load->view('extension/payment/quickpaycheckout.tpl', $data));
    }

    /**
     * Used to validate user input
     */
    private function validate()
    {
        $errors = [];
        //validate merchant name
        if (isset($this->request->post['quickpaycheckout_merchant_name']))
        {
            //check if the length of merchant name is between 30 and 1
            if (strlen($this->request->post['quickpaycheckout_merchant_name']) > 30 || strlen($this->request->post['quickpaycheckout_merchant_name']) < 1)
            {
                $errors["error_merchant_name"] = "Merchant Name should be between 1 and 30 characters";
            }
        } else
        {
            $errors["error_merchant_name"] = "Merchant Name is required";
        }
        //check if merchant description exists
        if (isset($this->request->post['quickpaycheckout_merchant_desc']))
        {
            //check if description is between 30 and 1 
            if (strlen($this->request->post['quickpaycheckout_merchant_desc']) > 50 || strlen($this->request->post['quickpaycheckout_merchant_desc']) < 1)
            {
                $errors["error_merchant_desc"] = "Merchant Description should be between 1 and 50 characters";
            }
        } else
        {
            $errors["error_merchant_desc"] = "Merchant Description is required";
        }
        //check if button text exists
        if (isset($this->request->post['quickpaycheckout_payment_button']))
        {
            //check if the payment button is between 20 and 1
            if (strlen($this->request->post['quickpaycheckout_payment_button']) > 20 || strlen($this->request->post['quickpaycheckout_payment_button']) < 1)
            {
                $errors["error_merchant_button"] = "Button Text should be between 2 and 20 characters";
            }
        } else
        {
            $errors["error_merchant_button"] = "Button Text is required";
        }
        return $errors;
    }

    public function order($param = null)
    {
        if (isset($this->request->get['order_id']))
        {
            $order_id = $this->request->get['order_id'];
        } else
        {
            $order_id = 0;
        }
        $data['text_amount'] = $this->language->get('text_amount');
        $data['text_currency'] = $this->language->get('text_currency');
        $data['text_description'] = $this->language->get('text_description');
        $data['merchant_id'] = $this->language->get('merchant_id');
        $data['order_info'] = $this->language->get('order_info');
        $data['receipt_no'] = $this->language->get('receipt_no');
        $data['auth_id'] = $this->language->get('auth_id');
        $data['transaction_no'] = $this->language->get('transaction_no');
        $data['reference_no'] = $this->language->get('reference_no');
        $data['transaction'] = $this->language->get('text_transaction');
        $data['token'] = $this->language->get('token');
        $data['response_code'] = $this->language->get('response_code');
        $result = $this->model_extension_payment_quickpaycheckout->getTransaction($order_id);
        $data['order_amount'] = isset($result['amount']) ? $result['amount'] : '';
        $data['order_description'] = isset($result['description']) ? $result['description'] : '';
        $data['order_response_code'] = isset($result['response_code']) ? $result['response_code'] : '';
        $data['order_transaction_no'] = isset($result['transaction_id']) ? $result['transaction_id'] : '';
        $data['order_reference_no'] = isset($result['reference_no']) ? $result['reference_no'] : '';
        $data['order_auth_id'] = isset($result['authentication_id']) ? $result['authentication_id'] : '';
        $data['order_token'] = isset($result['request_token']) ? $result['request_token'] : '';
        $data['order_auth_id'] = isset($result['authentication_id']) ? $result['authentication_id'] : '';
        $data['order_receipt_no'] = isset($result['receipt_no']) ? $result['receipt_no'] : '';
        $data['order_auth_id'] = isset($result['authentication_id']) ? $result['authentication_id'] : '';
        // $data['order_auth_id']  = isset($result['authentication_id']) ? $result['authentication_id'] : '';
        $data['order_order_info'] = isset($result['order_info']) ? $result['order_info'] : '';
        $data['order_currency'] = isset($result['currency']) ? $result['currency'] : '';
        return ($this->load->view('extension/payment/quickpaycheckoutinfo.tpl', $data));
    }

    /**
     * Install default application settings
     */
    public function install()
    {
        $this->model_extension_payment_quickpaycheckout->createSchema();
        //intialize plugin default settings
        $settings["quickpaycheckout_payment_button"] = "Checkout";
        $settings["quickpaycheckout_merchant_desc"] = "Quickpay Checkout";
        $settings["quickpaycheckout_sort_order"] = 1;
        $settings["quickpaycheckout_environment"] = 0;
        $this->model_setting_setting->editSetting('quickpaycheckout', $settings);
    }
    /**
     * Remove previous uploaded image
     */
    private function clearExistingIcon(){
        $config_url = "../" . $this->config->get("quickpaycheckout_payment_icon");
        if (file_exists($config_url) && $config_url !== "../")
        {
            unlink($config_url);
        }
    }

    /**
     * Remove application settings
     */
    public function uninstall()
    {
        //clear saved image icon
        $this->clearExistingIcon();
        $this->model_extension_payment_quickpaycheckout->deleteSchema();
        $this->model_setting_setting->deleteSetting('quickpaycheckout');
    }

}
