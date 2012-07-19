<?php

namespace hoverboard\workers;

/**
 * A wrapper class for SwiftMailer
 *
 */
class Mailer
{
	protected $transport;
	protected $mailer;
	protected $message;

	public function __construct()
	{
		$this->transport = \Swift_MailTransport::newInstance();
		$this->mailer = \Swift_Mailer::newInstance($this->transport);
	}

	public function __call($method, $args)
	{
		// echo "<pre>MAGIC CALLED METHOD: " . $method . "</pre>";
		$isMessageSetter = substr($method, 0, 10) === "setMessage";
		$setTarget = substr($method, 10);
		if ($isMessageSetter && get_class($this->message) === "Swift_Message") {
			$method = "set" . ucwords($setTarget);
			call_user_func_array(array($this->message, $method), $args);	
		}
		return $this;
	}

	public function newMessage()
	{
		$this->message = \Swift_Message::newInstance();
		return $this;
	}

	public function sendMessage()
	{
		return $this->mailer->send($this->message);
	}

	public function getMailerObject()
	{
		return $this->mailer;
	}

	public function getMessageObject()
	{
		return $this->message;
	}

}