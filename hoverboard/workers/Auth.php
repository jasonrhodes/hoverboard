<?php

namespace hoverboard\workers;
use \Slim;

class Auth
{
	protected static $sessionKey = "hm-check";

	public static function check($redirect = "/manager/login")
	{
		$users = new \storefront\app\models\Users();
		$router = Router::getInstance();
		$matchedRoute = $router->request()->getResourceUri();

		// First check session
		if ($userID = Session::get(static::$sessionKey)) {
			$user = $users->findByID($userID);
			return true;
		}

		// if ($matchedRoute === $redirect && $router->request()->isGet()) {
		// 	return false;
		// }

		if ($router->request()->isPost()) {
			// Check request variables from the router
			extract($router->request()->params() + array("username" => false, "password" => false));
		
			if ($username && $password) {
				$hashed = Hash::create($password);
				$user = $users->findByUsername($username);

				$typoMessage = "You typed the username or the password wrong. We know which one, but we're not telling.";

				if (!$user) {
					Messages::push("error", "DENIED. {$typoMessage}");
					return false;
				}

				if ($user["deleted"] === 1) {
					Messages::push("error", "This is embarassing. That user no longer exists.");
					return false;
				}

				if (Hash::verify($password, $user["hashed_password"])) {
					static::login($user);
					return true;
				} else {
					// incorrect password
					Messages::push("error", "OH SNAP. {$typoMessage}");
					return false;
				}
			} else {
				// username or password were left blank
				Messages::push("error", "Um, you need to enter a username and a password to log in. Are you new to the internet?");
				return false;
			}
		}

		// echo Router::$request->getResourceUri(); die();

		// Do NOT redirect if the route you're on is the same one you want to redirect to
		// (ie the log in page) -- there should be a better way to handle this...
		if ($redirect && $matchedRoute !== $redirect) {
			Messages::push("alert", "Whoa there, you have to be logged in to do that!");
			$router->redirect($redirect);
		}
		return false;

	}

	public static function login($user)
	{
		Session::set(static::$sessionKey, $user["id"]);
		Session::set("hubuser", serialize(array("id" => $user["id"], "name" => $user["username"], "role" => $user["role"])));
		Messages::push("success", "Welcome back, " . $user["username"] . "! Have you lost weight?");
	}

	public static function loggedInUser()
	{	
		$user = Session::get("hubuser");
		return $user ? (object) unserialize($user) : false;
	}


	public static function logout()
	{
		Session::destroy();
		Messages::push("info", "Come back soon!");
	}


	public static function createPassword($rawPassword)
	{
		return Hash::create($rawPassword);
	}


}