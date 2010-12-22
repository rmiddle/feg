<?php
/**
 * HylaFAX management
*/

/*
 *		JobFmt: "%-3j %3i %1a %15o %40M %-12.12e %5P %5D %7z %.25s"
 */

/**
 * CLASS: FaxQueue
	METHODS:
		public function __construct()
		public function exec_sendfax()
        public function process_queue()
		public function process_failed_queue()
        public function get_queue()
        public function faxalter()
        public function killjob($user, $jid)
 */

class FegFax {
	private	$queue,
			$keys = array('jid', 'pri', 's', 'owner', 'mailaddr', 'number', 'pages', 'dials', 'tts', 'status'),
			$fkeys = array('jid', 'pri', 's', 'owner', 'mailaddr', 'number', 'pages', 'dials', 'status');

	const FAXSTAT		= 'faxstat -s';
	const FAXSENDQ	= 'faxstat -s';
	const FAXDONEQ	= 'faxstat -d';
	const FAXRM			= 'faxrm';
	const FAXALTER		= 'faxalter';
	
	/**
	 * __construct
	 *
	 * @param integer num_modems
	 * @return void
	 * @access public
	 */
	public function __construct() {
		$this->process_queue();
	}
	
	/**
	 * exec_sendfax
	 *
	 * @param string command
	 * @return array
	 */
	function sendFax($phone_number, $message, $subject, $to, $account_name, $from=null) {
		// sendfax -f "robert.middleswarth@answernet.com" -D -R -r "Test Fax" -x "Account Name" -d "RecipientName@4106311699" /home/rmiddle/test.txt
		$settings = DevblocksPlatform::getPluginSettingsService();
		
		if(empty($from))
			@$from = $settings->get('feg.core',FegSettings::DEFAULT_REPLY_FROM, $_SERVER['SERVER_ADMIN']);

		$command = "sendfax ";
		$command .= sprintf("-f '%s' ", $from);
		$command .= "-D -R ";
		$command .= sprintf("-r '%s' ", $subject);
		$command .= sprintf("-x '%s' ", $account_name);
		$command .= sprintf("-d '%s@%s' ", $to, $phone_number);
		
		$tempfilename = tempnam(APP_TEMP_PATH . "/fax_cache", 'fax_message-');
		$temp_fh = fopen($tempfilename,'w') or die($php_errormsg);
		fputs($temp_fh, $message);
		fclose($temp_fh) or die($php_errormsg);		// Generate Text file of message.
		
		$command .= sprintf("'%s' ", $tempfilename);
		
		$o = exec($command." 2>&1", $sendfax_output, $retval);
		
		$debug = DEBUG_MODE;
		
		if ($retval == 0) {// success
			if ($debug) { echo "<p>$command"; }
			
			$result = str_replace("(", "", $sendfax_output[0]);
			$result = str_replace(")", "", $result);
			//	request id is 80 (group id 80) for host localhost (3 files)
			//	request id is 81 (group id 81) for host localhost (1 file)
			$output = split(" ", $result);
			
			return array('jobid' => $output[3], 'groupid' => $output[6], 'host' => $output[9], 'numfiles' => $output[10]);
		}
		
		if ($debug) {
			echo "<p>"; print_r($o); echo "<p>"; print_r($sendfax_output); 	echo "<p>$command";
		}
		
		$forlog = implode("\n", $sendfax_output);
		
		return $forlog;
	}
	
	/**
	 * process_queue
	 *
	 * @param integer num_modems
	 * @return void
	 * @access public
	 */
	public function process_queue() {
		exec(FAXSENDQ, $output);
		array_shift($output); 		// HylaFAX scheduler on ...
		
		foreach ($output as $line) {
			if (preg_match("/^Modem /", $line)) {	// match "/^Modem/
				array_shift($output);				// remove entry from array
			} else {
				break;
			}
		}
		
		array_shift($output);		// blank line
		array_shift($output);		// Title line: JID  Owner Number Dials
		
		$this->queue = array();
		
		if (is_array($output)) {
			$i = 0;
			$indices = count($this->keys) -1;
			
			foreach ($output as $q) {
				$arr = split(" ", $q);
				$j = 0;
				
				foreach ($arr as $a) {
					if ($a) {
						if ($j < $indices) {
							$this->queue[$i][$this->keys[$j]] = $a;
							$j++;
						} else {
							if (isset($this->queue[$i][$this->keys[$j]])) {
								$this->queue[$i][$this->keys[$j]] .= " $a";
							} else {
								$this->queue[$i][$this->keys[$j]] = $a;
							}
						}
					}
				}
				$i++;
			}
		}
	}
	
	/**
	 * process_failed_queue
	 *
	 * @param integer num_modems
	 * @return void
	 * @access public
	 */
	public function process_failed_queue() {
		exec(FAXDONEQ, $output);
		array_shift($output); 		// HylaFAX scheduler on ...
		
		foreach ($output as $line) {
			if (preg_match("/^Modem /", $line)) {	// match "/^Modem/
				array_shift($output);				// remove entry from array
			} else {
				break;
			}
		}
		
		array_shift($output);		// blank line
		array_shift($output);		// JID  Owner Number Dials
		
		$this->queue	= array();
		$queue			= array();
		
		if (is_array($output)) {
			$i = 0;
			$indices = count($this->fkeys) - 1;
			
			foreach ($output as $q) {
				$arr = split(" ", $q);
				$j = 0;
				
				foreach ($arr as $a) {
					if ($a) {
						if ($j < $indices) {
							$queue[$i][$this->fkeys[$j]] = $a;
							$j++;
						} else {
							if (isset($queue[$i][$this->fkeys[$j]])) {
								$queue[$i][$this->fkeys[$j]] .= " $a";
							} else {
								$queue[$i][$this->fkeys[$j]] = $a;
							}
						}
					}
				}
				$i++;
			}
			
			foreach ($queue as $q) {
				if ($q['s'] == 'F')
					$this->queue[] = $q;
			}
		}
	}
	
	/**
	 * get_queue
	 *
	 * @return array
	 * @access public
	 */ 
	public function get_queue() {
		$ret	= array();
		
		foreach ($this->queue as $q) {
			$ret[] = $q;
		}
		
		return $ret;
	}
	
	/**
	 * killjob
	 *
	 * @param string user
	 * @param integer jid
	 * @return bool
	 * @access public
	 */
	public function killjob($jid) {
		$res = shell_exec("FAXRM $jid");
		//$res = shell_exec("export FAXUSER=$user; $FAXRM $jid; unset FAXUSER");
		return true;
	}
	
	/**
	 * faxalter
	 *
	 * @param string user
	 * @param integer jid
	 * @param array operations
	 * @return bool
	 * @access public
	 */
	public function faxalter($jid, array $operations) {
		$ops		= "";
		$killjob	= false;
		
		foreach ($operations as $op => $value) {
			switch ($op) {
				case 'resubmit':	$ops .= ' -r ';		$killjob	= true; break;
				case 'sendtime':	$ops .= ' -a "'.	$value.'"'; break;
				case 'destination':	$ops .= ' -d "'.	$value.'"'; break;
				case 'killtime':	$ops .= ' -k "'.	$value.'"'; break;
				case 'device':		$ops .= ' -m "'.	$value.'"'; break;
				case 'priority':	$ops .= ' -P "'.	$value.'"'; break;
				case 'tries':		$ops .= ' -t "'.	$value.'"'; break;
			}
		}
		
		$res = shell_exec("FAXALTER $ops $jid");
		
		if ($killjob) {
			$this->killjob($jid);
		}
		
		return true;
	}
}
