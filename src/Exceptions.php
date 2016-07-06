<?php
/**
 * This file contains all exception classes that may be thrown by method of the LoginLib class
 */

/**
 * Exception class for LoginLib methods
 */
class MethodException extends Exception {
	/**
	 * The constrcutor of MethodExceptions just use the default exception class atm
	 * 
	 * @param string    $message  The message of the exception
	 * @param int       $code     The code of the exception
	 * @param Exception $previous The previous exception
	 * 
	 * @return MethodException
	 */
	public function __construct($message = "", $code = 0, $previous = null) {
		parent::__construct($message, $code, $previous);
	}
}