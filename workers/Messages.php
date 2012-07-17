<?php

namespace hoverboard\workers;

/**
 * @class Messages
 *
 * Small worker class to store and pass messages from the application,
 * so that the view's render function can access them and provide them
 * all, from one place, to the template view files.
 *
 * Example usage from within a model:
 *
 * public function findAll()
 * {
 *     $all = queryAllFromDatabaseSomehow();
 *     if (count($all) === 0) { Messages::push("error", "No records were returned from the database."); }
 * }
 *
 * Usage from within the view:
 *
 * public function render($template)
 * {
 *     $this->engine->render($template, $this->model->data + Messages::pull());
 * }
 */
class Messages
{
    /**
     * @var array $messages The messages array that will hold several
     * named arrays of various types of messages.
     */
    protected static $messages = array();


    /**
     * @param string $type A single word name that will be used to create
     * arrays to store the message in.
     * @param string $message The message to be stored and output later.
     * @return null
     */
    public static function push($type, $message)
    {
        //static::$messages[$type][] = $message;
        Session::push("messages", array($type => array($message)));
    }


    /**
     * @param boolean $clear Whether or not to clear the static $messages
     * array. Defaults to true because you always want to do this.
     */
    public static function pull($clearMessages = true)
    {
        // $messages = array( "messages" => static::$messages );
        // if ($clearMessages) {
        //     static::$messages = array();
        // }
        // return $messages;
        $messages = Session::get("messages");
        if ($clearMessages) {
            Session::set("messages", array());
        }
        return array("messages" => $messages);
    }
}