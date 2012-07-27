<?php

namespace hoverboard\base;
use \hoverboard\adapters\interfaces\TemplateEngine;
use \hoverboard\workers\Messages;
use \hoverboard\workers\Session;
use \hoverboard\workers\Router;
use \hoverboard\workers\Logger;

class View
{
    /**
     * @var object $engine
     *
     * The template engine object
     */
    protected static $engine = null;


    protected static $model;

    protected static $log;


    /**
     * @param object $engine The template engine object to use
     */
    public static function setEngine(TemplateEngine $engine)
    {
        static::$engine = $engine;
    }


    public static function setModel(Model $model)
    {
        static::$model = $model;
    }

    public static function setLogger(Logger $logger)
    {
        static::$log = $logger;
    }


    /**
     * @param string $template The relative path/name of the template
     */
    public static function render($template, $data = array())
    {
        $data = (array) $data;
        if (isset(static::$model->data) && is_array(static::$model->data)) {
            $data += static::$model->data;
        }
        $userdata = array();
        if ($currentUser = Session::get("hubuser")) {
            $userdata["user"] = unserialize($currentUser);
            if (!is_array($userdata["user"])) {
                $userdata["user"] = array("name" => "unknown", "error_message" => "Unserialize action did not result in an array");
            }
        }
        $router = Router::getInstance();
        $request = array("request" => array(
            "params" => $router->request()->params(),
            "rootUri" => $router->request()->getUrl(),
            "resourceUri" => $router->request()->getResourceUri()
        ));
        return static::$engine->render($template, $userdata + $data + Messages::pull() + $request);
    }


    public static function setLayout($layout)
    {
        //var_dump(static::$model->data); die();
        static::$model->data["layout"] = static::$engine->loadTemplate("layouts/{$layout}.twig");
    }


}