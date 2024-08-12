<?php
namespace Opencart\System\Library\Controller\Extension\Sendgridapimail;
class SendgridapiMail {
	protected $to;
	protected $from;
	protected $sender;
	protected $reply_to;
	protected $subject;
	protected $text;
	protected $html;
	protected $api_key;
	
	
	public function __construct($api_key) {
		$this->api_key = $api_key;
	}

	public function setTo($to) {
		$this->to = $to;
	}

	public function setFrom($from) {
		$this->from = $from;
	}

	public function setSender($sender) {
		$this->sender = $sender;
	}

	public function setReplyTo($reply_to) {
		$this->reply_to = $reply_to;
	}

	public function setSubject($subject) {
		$this->subject = $subject;
	}

	public function setText($text) {
		$this->text = $text;
	}

	public function setHtml($html) {
		$this->html = $html;
	}

	public function send() {
		if (!$this->to) {
			throw new \Exception('Error: E-Mail to required!');
		}

		if (!$this->from) {
			throw new \Exception('Error: E-Mail from required!');
		}

		if (!$this->sender) {
			throw new \Exception('Error: E-Mail sender required!');
		}

		if (!$this->subject) {
			throw new \Exception('Error: E-Mail subject required!');
		}

		if ((!$this->text) && (!$this->html)) {
			throw new \Exception('Error: E-Mail message required!');
		}

		if (is_array($this->to)) {
			$to = implode(',', $this->to);
		} else {
			$to = $this->to;
		}
		$textorder   = array("\r\n", "\n", "\r", PHP_EOL);
					
		$mailtext = str_replace($textorder, "<br/>", $this->text);

		$datamessage = isset($this->html) ? $this->html : $mailtext;	
		
		$sendmaildata = Array(
				"personalizations" => Array(
					0 => Array(
						"to" => Array(
							0 => Array(
								"email" => $to,
								"name" => "Value Customer",
								)),
						"subject" => $this->subject),
								), 
				"from" => Array(
					"email" => $this->from,
					"name" => $this->sender
						), 
				"reply_to" => Array(
					"email" => $this->from,
					"name" => $this->sender
						), 
				"subject" => $this->subject,
				"content" => Array(
					0 => Array(
						"type" => "text/html",
						"value" => $datamessage
							))
						);		
				$curl = curl_init();
				curl_setopt_array($curl, array(
				  CURLOPT_URL => "https://api.sendgrid.com/v3/mail/send",
				  CURLOPT_RETURNTRANSFER => true,
				  CURLOPT_ENCODING => "",
				  CURLOPT_MAXREDIRS => 10,
				  CURLOPT_TIMEOUT => 30,
				  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				  CURLOPT_SSL_VERIFYHOST => false,
				  CURLOPT_SSL_VERIFYPEER => false,
				  CURLOPT_CUSTOMREQUEST => "POST",
				  CURLOPT_POSTFIELDS => json_encode($sendmaildata),
				  CURLOPT_HTTPHEADER => array(
					"authorization: Bearer ". $this->api_key,
					"content-type: application/json"
				  ),
				));

				$response = curl_exec($curl);
				$err = curl_error($curl);
				curl_close($curl);
				
				echo $err;
	}

}
