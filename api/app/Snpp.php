<?php

class FegSnpp {
	/**
	 * Enter description here...
	 *
	 * @param string $string
	 * @return array
	 */
	static function sendSnpp($phone_number, $message, $snpp_server="ann100sms01.answernet.com", $port=444) {
		try {
			// snpp -s ann100sms01.answernet.com:444 -n -m "actual message" phone_number
			$command = "snpp -s " . $snpp_server . ":" . $port . ' -n -m "'. $message .'" ' . $phone_number . '2>&1';
			$o = exec($command, $snpp_output, $retval);

echo "<pre>";
print_r($phone_number);
echo "<br>";
print_r($message);
echo "<br>";
print_r($command);
echo "<br>";
echo "retval: " . $retval . "<br>";
print_r($o);
echo "<br>";
echo "</pre>";
			if ($retval == 0) {// success
				// Do something when successful
			} else {
				return false;
			}

		} catch (Exception $e) {
			return false;
		}
		
		return true;
	}
	
}