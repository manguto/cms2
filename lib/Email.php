<?php

namespace manguto\cms\lib;


class Email {
	
	
	// imap server connection
	public $conn;
	
	// inbox storage and inbox message count
	private $inbox;
	public $msg_cnt;
	
	// email login credentials
	private $server = 'yourserver.com';
	private $user   = 'email@yourserver.com';
	private $pass   = 'yourpassword';
	private $port   = 143; // adjust according to server settings
	
	// connect to the server and get the inbox emails
	function __construct($server,$user,$pass,$port='143') {
		
		$this->server = $server;
		$this->user = $user;
		$this->pass = $pass;
		$this->port = $port;
				
		$this->connect();
	}
	
	// close the server connection
	function close() {
		$this->inbox = array();
		$this->msg_cnt = 0;
	
		imap_close($this->conn);
	}
	
	// open the server connection
	// the imap_open function parameters will need to be changed for the particular server
	// these are laid out to connect to a Dreamhost IMAP server
	function connect() {
		$this->conn = imap_open('{'.$this->server.'/notls}', $this->user, $this->pass);
		self::msg_cnt();
	}
	
	// move the message to a new folder
	function move($msg_index, $folder='INBOX.Processed') {
		// move on server
		imap_mail_move($this->conn, $msg_index, $folder);
		imap_expunge($this->conn);	
		
	}
	
	// read the inbox
	function get_all() {			
		$in = array();
		for($i = 1; $i <= $this->msg_cnt; $i++) {
			$in[] = self::get($i);
		}	
		$this->inbox = $in;
	}
	
	// get a specific message (1 = first email, 2 = second email, etc.)
	function get($msg_index) {
		if(intval($msg_index) && ($this->msg_cnt > 0) && ($msg_index > 0 ) && ($msg_index <= $this->msg_cnt)){
			$return = array(
					'index'     => $msg_index,
					'header'    => imap_headerinfo($this->conn, $msg_index),
					'body'      => imap_body($this->conn, $msg_index),
					'structure' => imap_fetchstructure($this->conn, $msg_index)
			);
		}else{
			$return  = array();
		}		
		return $return;
	}	
	
	function msg_cnt(){
		$this->msg_cnt = imap_num_msg($this->conn);
		//debug($this->msg_cnt);
	}
	
	
	
	
	/**
	 * Envia um email com as informacoes fornecidas	 
	 */
	static function Enviar($from,$to,$cc="",$cco="",$subject,$content,$style='font-family:Verdana; color:#333;') {
		
		$headers = "From: $from \r\n";
		$headers .= "Reply-To: $from \r\n";
		$headers .= $cc!="" ? "CC: $cc\r\n" : "";
		$headers .= $cco!="" ? "CCO: $cco\r\n" : "";
		$headers .= "MIME-Version: 1.0\r\n";
		$headers .= "Content-Type: text/html; charset=UTF-8\r\n";
				
		$message = "<html>
<head>
<title>HTML email</title>
</head>
<body style='$style'>
	$content
</body>
</html>";
		
		$sendMail = mail ( $to, $subject, $message, $headers );
		
		if(!$sendMail){
			echo "<h2 style='color:#f00; background-color:#ff0;'>ERRO: $sendMail</h2>";
		}
	}
}







?>