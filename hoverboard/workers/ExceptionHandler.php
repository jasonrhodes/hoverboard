<?php

namespace hoverboard\workers;

/**
 * @class ExceptionHandler
 * 
 * By instantiating this class in your bootstrap (liftoff.php) file, you 
 * get this exception handler by default. If you prefer, you can extend
 * this class and overwrite the handle method to handle your exceptions
 * however you like.
 *
 * Two notes:
 * 1.   By handling exceptions this way, execution will stop once the exception
 *      has been handled. If you use try/catch, you can let execution continue
 *      based on the error's severity, etc.
 * 2.   When using a routing engine like Slim, it overtakes error handling and
 *      submits your Exception messages through its own system. The Slim handler
 *      does a pretty good job and provides a detailed stack trace, so it's 
 *      probably better to let it handle the errors. But if you want this 
 *      exception handler to take over, you have to do some work with the router's
 *      config settings (ie in Slim it's $app->config("debug" = false)) and also
 *      $app->error(array($exceptionHandler, "handle"));
 */

class ExceptionHandler 
{
    public function __construct()
    {
        set_exception_handler(array($this, "handle"));
    }

    public function handle(\Exception $e)
    {
        echo "<div style='border-top: 1px solid black; padding: 20px 0;'>";
        echo "<div style='width: 800px; margin: 0 auto; font-size: 20px;'>";
        
        echo "<h2>Uh oh, the site did something bad.</h2>";
        echo "<p>Exception message: " . $e->getMessage() . "</p>";
        echo "<h5>Stack Trace</h5>";
        echo "<pre>" . print_r(debug_backtrace(), true) . "</pre>";

        echo "</div></div>";
    }
}