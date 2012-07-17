<?php

namespace hoverboard\adapters;
use \hoverboard\workers\Auth;
use \hoverboard\workers\Messages;
use \hoverboard\workers\Router;

class AuthCheckMiddleware extends \Slim_Middleware
{
	public function call()
	{
		$router = Router::getInstance();
		$matchedRoute = $router->request()->getResourceUri();
		$segments = explode("/", $matchedRoute);

		if ($matchedRoute != "/manager/login" && $segments[1] === "manager") {
			Auth::check();
		}

		// Rudimentary way of blocking access to certain areas of the manager
		// How do we set this up in a more robust way?
		$currentUser = Auth::loggedInUser();
		if ($currentUser && (count($segments) > 2) && $segments[2] === "users" && $currentUser->role !== "admin") {
			Messages::push("error", "You're not allowed to see that page ({$matchedRoute})!");
			$router->redirect("/manager");
		}
		
		$this->next->call();
	}
}