<?php

class ModelExtensionPaymentQuickpaycheckout extends Model {
   private $table_name;
   private $table_order;
   private $order_primary_key = "order_id";
   private $primary_key = "id";
   private $order_id = "order_id";
   private $trans_id = "transaction_id";
   private $auth_id = "authentication_id";
   private $merchant_id = "merchant_id";
   private $ref_no = "reference_no";
   private $token = "request_token";
   private $receipt_no = "receipt_no";
   private $currency = "currency";
   private $amount = "amount";
   private $order_info = "order_info";
   private $response_code = "response_code";
   private $created_at = "created_at";
   private $description = "description";
   
   function __construct($param){
       parent::__construct($param);
       $this->table_name = DB_PREFIX."quickpaycheckout_transactions";
       $this->table_order = DB_PREFIX."order";
   }
   /**
    * created default tables required by the module
    */  
   public function createSchema() {
      $this->db->query("
      CREATE TABLE IF NOT EXISTS ".$this->table_name."("
              .$this->primary_key." BIGINT PRIMARY KEY AUTO_INCREMENT, "
              .$this->order_id." INT(11), "
              .$this->response_code." TINYINT, "
              .$this->trans_id." TEXT, "
              .$this->auth_id." TEXT, "
               .$this->merchant_id." TEXT, "
              .$this->ref_no." TEXT, "
              .$this->token." TEXT, "
              .$this->receipt_no." TEXT, "
              .$this->currency." VARCHAR(10), "
              .$this->amount." VARCHAR(200), "
              .$this->order_info." TEXT, "
              .$this->description." TEXT, "
              .$this->created_at." TIMESTAMP DEFAULT CURRENT_TIMESTAMP, "
              . "INDEX (".$this->order_id."), "
              . "CONSTRAINT FOREIGN KEY ".$this->table_name."("
              .$this->order_id.") REFERENCES "
              .$this->table_order."(".
              $this->order_primary_key.") ON DELETE CASCADE"
              . ")ENGINE MyISAM");
   }
   /**
    * remove tables associated with the module. Due to PCI Compliance standards we can't delete transactions table
    */
   public function deleteSchema() {
      //$this->db->query("DROP TABLE IF EXISTS ".$this->table_name."");
   }
   
   public function getTransaction($order_id){
       $result = $this->db->query("select * from ".$this->table_name." where ".$this->order_id."='".$this->db->escape($order_id)."'");
       try{
           return $result->row;  
       } catch (Exception $ex) {
           return false;
       }   
            
   }
   


}
