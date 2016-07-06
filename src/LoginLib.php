<?php
/**
 * This file contains the LoginLib class
 * 
 * The LoginLib class contains all the logic and mechanisms for it to work properly
 */
namespace LoginLib;
use MysqliDb\MysqliDb as MysqliDb;

/**
 * A class that provides the background mechanics for login and registration forms
 * 
 * Use this class to authenticate your users on your website. Design a corresponding login/registration page on your website and this class will do the logic behind it.
 * 
 * @category  Library
 * @package   LoginLib
 * @author    Ricardo (MCMainiac) Boss <ricardo.boss@web.de>
 * @copyright &copy; 2016
 * @license   https://creativecommons.org/licenses/by-sa/4.0/ Creative Commons BY SA 4.0
 * @link      https://github.com/MCMainiac/LoginLib
 * @version   0.1.0
 */
class LoginLib {
	/** @var array Used to store the configuration array of LoginLib */
	private $config;
	
	/** @var MysqliDb The database class object used to communitcate with the database */
	private $db;

	/**
	 * The constructor of LoginLib.
	 * 
	 * @param array $config The configuration array of LoginLib
	 * 
	 * @return LoginLib
	 */
	public function __construct($config) {
		$this->config = $config;
		
		$this->db = new MysqliDb($config['database']);
		// TODO: check if tables exist, if not => create them
	}

	/**
	 * Call this method to authenticate a registered user
	 * 
	 * @param string   $username The username or email-address of the user
	 * @param string   $password The password or key the user provides
	 * @param function $callback A callback function that gets called when the function finished processing
	 * 
	 * @return LoginResult
	 */
	public function login($username, $password, $callback = null) {
		// check the db, just in case a script runs very long
		checkDb();
		
		// add where selector
		$db->where($this->config['table']['accounts']['col_username'], $username);
		
		// get one row only
		$account = $db->getOne($this->config['table']['accounts']['name']);
		
		// if the result is an account, proceed, otherweise return that no account is associated with that username/email address
		if (isset($account)) {
			// check if the password hashs are equal
			if (hash_equals($account[$config['table']['accounts']['col_password_hash']], crypt($password, $account[$config['table']['accounts']['col_password_hash']]))) {
				// if they are, the user is logged in
				
				// TODO: cookie and session stuff for login checks and so on 
				
				$result = new LoginResult(LoginResult::SUCCESS);
			} else {
				$result = new LoginResult(LoginResult::PASSWORD_WRONG);
			}
		} else {
			$result = new LoginResult(LoginResult::USERNAME_NOT_FOUND);
		}

		// call the callback in case one was specified
		if ($callback !== null)
			$callback($result);
	
		// return the result anyway
		return $result;
	}

	/**
	 * This method is used to register a new user
	 * 
	 * @param string   $username The username
	 * @param string   $email    The email address
	 * @param string   $password The password
	 * @param string   $confirm  The password confirmation
	 * @param function $callback A callback function that gets called when the function finished processing
	 *
	 * @return RegisterResult
	 */
	public function register($username, $email, $password, $confirm, $callback = null) {
		// TODO: do database and logic things
		$result = new RegisterResult(RegisterResult::SUCCESS);

		if ($callback !== null)
			$callback($result);

		return $result;
	}
	
	/**
	 * This static function return true if the browser is logged in or false if not
	 * 
	 * @return bool
	 */
	public static function isLoggedIn() {
		// TODO: do cookie and session and database checks and so on to authenticate the user
		return false;
	}
	
	
	/**
	 * Check the database connection, ping it or reconnect if neccessary
	 * 
	 * @return void
	 */
	private function checkDb() {
		if (!$this->db->ping)
			$this->db->connect();
	}
}
