<?php 

/**
* 
*/
use paragraph1\phpFCM\Client;
use paragraph1\phpFCM\Message;
use paragraph1\phpFCM\Recipient\Device;
use paragraph1\phpFCM\Notification;

require_once 'vendor/autoload.php';
require  'PayPal-PHP-SDK/autoload.php';

class genral_model extends CI_Model
{
	
	function __construct()
	{
		parent::__construct();
	}

	public function sendPush($pushData) {
		// print_r($pushData);
		
		//Save notification logs	
		$this->db->insert('tbl_notification',array("user_id"=>$pushData['user_id'],"message"=>$pushData['message'],"amount"=>$pushData['amount']));
		try {
			$apiKey = 'AIzaSyDUvYURUtGBz_bwsBLzxzbawhX3K9Qw87U';
			$client = new Client();
			$client->setApiKey($apiKey);
			$client->injectHttpClient(new \GuzzleHttp\Client());

			$note = new Notification('Title', $pushData['message']);	

			$message = new Message();
			$message->addRecipient(new Device($pushData['registration_id']));
			$message->setNotification($note);
			    

			$response = $client->send($message);
			//var_dump($response->getBody()->getContents());			
			return true;			
		} catch (Exception $e){
			//var_dump($e);
			//exit;
			return false;
		}
	}

	public function payMoney($data){
		/*return false;
		exit;*/
		$this->config->load('paypal');
		// exit;
		$apiContext = new PayPal\Rest\ApiContext(new PayPal\Auth\OAuthTokenCredential($this->config->item('client_id'),$this->config->item('client_secret')));
		// //Set redirection urls        
		// $redirectUrls = new PayPal\Api\RedirectUrls();
		// $redirectUrls->setReturnUrl($this->config->item('PayPalReturnURL').'?booking_id='.$booking_id);
		// $redirectUrls->setCancelUrl($this->config->item('PayPalCancelURL').'?booking_id='.$booking_id);
		$payouts = new \PayPal\Api\Payout();
	
		$senderBatchHeader = new \PayPal\Api\PayoutSenderBatchHeader();
		
		$senderBatchHeader->setSenderBatchId(uniqid())
		    ->setEmailSubject("You have a Payout!");
		
		$senderItem = new \PayPal\Api\PayoutItem();
		$senderItem->setRecipientType('Email')
		    ->setNote('Thanks for your referring user to Go solar!')
		    ->setReceiver($data['email'])
		    ->setSenderItemId("2014031400023")
		    ->setAmount(new \PayPal\Api\Currency('{
		                        "value":'.$data['amount'].',
		                        "currency":"USD"
		                    }'));
		 
		$payouts->setSenderBatchHeader($senderBatchHeader)
		    ->addItem($senderItem);
		// For Sample Purposes Only.
		$request = clone $payouts;
		try {
    		$output = $payouts->createSynchronous($apiContext);    		
    		// var_dump($output->getBatchHeader()->getPayoutBatchId());
    		return true;
		} catch (Exception $ex) {
			// var_dump($ex);	     
			return false;	    	
		}

		// echo  $output->getBatchHeader()->getPayoutBatchId();
       
	}
}

?>
