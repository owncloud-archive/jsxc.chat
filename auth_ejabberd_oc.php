#!/usr/bin/php
<?php

/*
   Copyright (c) <2005> LISSY Alexandre, "lissyx" <alexandrelissy@free.fr>
   Copyright (c) <2014> Klaus Herberth <klaus@jsxc.org>

   Permission is hereby granted, free of charge, to any person obtaining a copy of
   this software andassociated documentation files (the "Software"), to deal in the
   Software without restriction, including without limitation the rights to use,
   copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the
   Software, and to permit persons to whom the Software is furnished to do so,
   subject to thefollowing conditions:

   The above copyright notice and this permission notice shall be included in all
   copies or substantial portions of the Software.

   THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
   IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
   FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
   COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
   IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
   CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

//#############Start#of#Config####################

/** The root of your owncloud installation. */
$oc_root = '/var/www/owncloud/';

/** Should we log the activity of this script? */
$debugging = true;

//#############End#of#config######################

error_reporting(E_ALL);
ini_set('error_log', '/var/log/ejabberd/oc.log');

$auth = new JabberAuth();
$auth->play(); // We simply start process !

class JabberAuth {

	/** This is the jabber user passed to the script. filled by $this->command() */
	var $jabber_user;   

	/** This is the jabber user password passed to the script. filled by $this->command() */
	var $jabber_pass;  

	/** This is the jabber server passed to the script. filled by $this->command(). Useful for VirtualHosts */
	var $jabber_server; 

	/** This is what SM component send to us. */
	var $data;          

	/** This is the command sent ... */
	var $command; 

	/** stdin file pointer */
	var $stdin;   

	/** stdout file pointer */
	var $stdout;  

	/** oc user object */
	var $ocUser;
	var $errCounter = 0;

	function JabberAuth()
	{
		$this->logg("Starting pipe-auth ..."); // We notice that it's starting ...
		$this->openstd();
	}

	function stop()
	{
		$this->logg("Shutting down ..."); // Sorry, have to go ...

		$this->closestd(); // Simply close files
		exit(0); // and exit cleanly
	}

	function openstd()
	{
		$this->stdout = @fopen("php://stdout", "w"); // We open STDOUT so we can read
		$this->stdin  = @fopen("php://stdin", "r"); // and STDIN so we can talk !
	}

	function readstdin()
	{
		$l      = @fgets($this->stdin, 3); // We take the length of string
		$length = @unpack("n", $l); // ejabberd give us something to play with ...
		$len    = $length["1"]; // and we now know how long to read.

		if($len > 0) { // if not, we'll fill logfile ... and disk full is just funny once
			$this->logg("Reading $len bytes ... "); // We notice ...
			$data   = @fgets($this->stdin, $len+1);
			// $data = iconv("UTF-8", "ISO-8859-15", $data); // To be tested, not sure if still needed.
			//We set what we got
			$this->data = $data;

			$data = preg_replace('/:[^:]+$/i', ':*******', $data);
			$this->logg("IN: ".$data);
		}
	}

	function closestd()
	{
		@fclose($this->stdin); // We close everything ...
		@fclose($this->stdout);
	}

	function out($message)
	{
		@fwrite($this->stdout, $message); // We reply ...
		$dump = @unpack("nn", $message);
		$dump = preg_replace('/:[^:]+$/i', ':********', $dump["n"]);
		$this->logg("OUT: ". $dump);
	}


	function play()
	{
		do {
			$this->readstdin(); // get data
			$length = strlen($this->data); // compute data length
			if($length > 0 ) { // for debug mainly ...
				$this->logg("GO: ".preg_replace('/:[^:]+$/', ':********', $this->data));
				$this->logg("data length is : ".$length);
			}
			$ret = $this->command(); // play with data !
			$this->logg("RE: " . $ret); // this is what WE send.
			$this->out($ret); // send what we reply.
			$this->data = NULL; // more clean. ...
		} while (true);
	}

	function command()
	{
		$data = $this->splitcomm(); // This is an array, where each node is part of what SM sent to us :
		// 0 => the command,
		// and the others are arguments .. e.g. : user, server, password ...

		if(strlen($data[0]) > 0 ) {
			$this->logg("Command was : ".$data[0]);
		}

		switch($data[0]) {
			case "isuser": // this is the "isuser" command, used to check for user existance
				$this->jabber_user = $data[1];
				$parms = $data[1];  // only for logging purpose
				$return = $this->checkuser();
				break;

			case "auth": // check login, password
				$this->jabber_user = trim($data[1]);
				$this->jabber_pass = trim($data[3]);
				$parms = $data[1].":".$data[2].":".md5($data[3]); // only for logging purpose
				$return = $this->checkpass();
				break;

			case "setpass":
				$return = false; // We do not want jabber to be able to change password
				break;

			default: 
				$this->stop(); // if it's not something known, we have to leave.
				// never had a problem with this using ejabberd, but might lead to problem ?
				break;
		}

		$return = ($return) ? 1 : 0;

		if(strlen($data[0]) > 0 && strlen($parms) > 0) {
			$this->logg("Command : ".$data[0].":".$parms." ==> ".$return." ");
		}
		return pack("nn", 2, $return);
	}

	function checkpass()
	{
		global $oc_root;

		$user = escapeshellarg($this->jabber_user);
		$pass = escapeshellarg($this->jabber_pass);

		$ret = exec("/etc/ejabberd/auth_oc_user.php '$oc_root' $user $pass");
		
		$this->logg("Password is " . ($ret == '1'));

		if ($ret != '1') {
			error_log("OC: Authentication failure for {$this->jabber_user}");
		}

		return $ret == '1';
	}

	function checkuser()
	{
		global $oc_root;

		$user = escapeshellarg($this->jabber_user);

		$ret = exec("/etc/ejabberd/auth_oc_user.php '$oc_root' $user");

		return $ret == '1';
	}

	function splitcomm() // simply split command and arugments into an array.
	{
		return explode(":", $this->data);
	}

	function logg($message) // pretty simple, using syslog.
	{
		global $debugging;

		if($debugging) {
			syslog(LOG_INFO, '[ejabberd-oc] '.$message);
		}
	}
}

?>
