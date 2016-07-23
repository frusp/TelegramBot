# TelegramBot

## Example code

  require "TelegramBot.php";

	$bot = new TelegramBot( "YOUR BOT ID HERE", "GoDaddy.crt" );

	$result = $bot->getUpdates();
	$last_offset = 0;

	foreach ( $result as $update ) {
		if ($last_offset < $update['update_id']) {
			$last_offset = $update['update_id'];
		}

		$message = @$update['message'];
		if ($message['text'] != null) {
			$username = $message['from']['username'];
			$text = $message['text'];

			// Send an answer
			$bot->sendMessage($message['chat']['id'], $response);
		}
	}
