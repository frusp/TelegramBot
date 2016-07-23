<?php

    // check if cURL is installed
    if (!function_exists('curl_init')){
        die('Aborting: cURL not installed!');
    }

class TelegramBot {
	private $BOTNAME = "bot";
	private $API_URL = "https://api.telegram.org/bot";
	private $TOKEN = "";
	private $CERT = "";

	public function __construct ($token, $cert) {
		$this->TOKEN = $token;
		$this->CERT = $cert;
	}

	private static function raw_send ($bot_name, $cert_file, $api_url, $bot_token, $command, $data = null) {
		$curl_options = array(
							CURLOPT_RETURNTRANSFER => true,     // return web page
							CURLOPT_HEADER         => false,    // don't return headers
							CURLOPT_FOLLOWLOCATION => true,     // follow redirects
							CURLOPT_ENCODING       => "",       // handle all encodings
							CURLOPT_USERAGENT      => $bot_name,    // who am i
							CURLOPT_AUTOREFERER    => true,     // set referer on redirect
							CURLOPT_CONNECTTIMEOUT => 120,      // timeout on connect
							CURLOPT_TIMEOUT        => 120,      // timeout on response
							CURLOPT_MAXREDIRS      => 10,       // stop after 10 redirects
							CURLOPT_SSL_VERIFYPEER => true,
							CURLOPT_CAINFO         => $cert_file
						);

		$curl = curl_init(  $api_url . $bot_token . "/" . $command );
			curl_setopt_array( $curl, $curl_options );

			if ( is_array($data) && count($data) > 0 ) {
				curl_setopt($curl, CURLOPT_POST, true );
				curl_setopt($curl, CURLOPT_POSTFIELDS, $data );
			}
			
			$result = curl_exec( $curl );

			$err     = curl_errno( $curl );
			$errmsg  = curl_error( $curl );
			$header  = curl_getinfo( $curl );

		curl_close( $curl );
		return $result;
	}

	private static function pack_post_data ( $data ) {
		//url-ify the data for the POST
		$fields_string = "";
		foreach ($data as $key=>$value) { $fields_string .= urlencode($key) .'='. urlencode($value) .'&'; }
		rtrim($fields_string, '&');
		return $fields_string;
	}

	private function send_command ($command, $data = null) {
		$raw_response = $this->raw_send($this->BOTNAME, getcwd()."/".$this->CERT, $this->API_URL, $this->TOKEN, $command, $data);
		$response = json_decode($raw_response, true);
		if ($response['ok'] == true) {
			return $response['result'];
		} else {
			// Server responded with error message
			// TODO: handle error information here
			return null;
		}
	}

	public function getMe () {
		$response = $this->send_command("getMe");
		return $response;
	}

	public function setWebhook ($url, $certificatePath) {
		$file_name_with_full_path = realpath('./'.$certificatePath);
		
		$fields = array( 'url' => $url,
						 'certificate' => '@'.$file_name_with_full_path
						);
						
		$content = $this->send_command( "setWebhook", $fields );
		return $content;
	}

	public function unsetWebhook () {
		$fields = array( 'url' => '',
						 'certificate' => ''
						);
						
		$content = $this->send_command( "setWebhook", $fields );
		return $content;
	}

	public function sendMessage ($chat_id, $text) {
		$fields = array( 'chat_id' => $chat_id,
						 'text' => $text
						);

		$content = $this->send_command( "sendMessage", $fields );
		return $content;
	}

	public function getUpdates($offset = 0) {
		$fields = array( 'offset' => $offset,
					   );
		$content = $this->send_command( "getUpdates", $fields );
		return $content;
	}

}