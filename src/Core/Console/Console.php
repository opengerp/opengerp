<?php

namespace Opengerp\Core\Console;


class Console
{

    /**
     * @var Message[]
     */
    private static $messages;

    public function __construct()
    {
        if ( ! isset($_SESSION['gerp_console']['messages'])) {
            $_SESSION['gerp_console']['messages'] = [];
        }

        self::$messages = $_SESSION['gerp_console']['messages'];

    }

    public static function appendError($str_errore, $params = [])
    {

        $obj_msg = new ErrorMessage($str_errore, $params);

        self::$messages[] = $obj_msg;
        $_SESSION['gerp_console']['messages'][] = $obj_msg;


    }




    public static function appendSuccess($str_success, $params = [])
    {
        $obj_msg = new SuccessMessage($str_success, $params);

        self::$messages[] = $obj_msg;
        $_SESSION['gerp_console']['messages'][] = $obj_msg;
    }

    public static function appendWarning($str_warning, $params = [])
    {
        $obj_msg = new WarningMessage($str_warning, $params);

        self::$messages[] = $obj_msg;
        $_SESSION['gerp_console']['messages'][] = $obj_msg;
    }

    public static function appendLog($log_content, $params = [])
    {
        global $obj_utente;

        if (! $obj_utente || ! $obj_utente->hasDebugMode() ) {
            return false;
        }

        $obj_msg = new LogMessage($log_content, $params);

        self::$messages[] = $obj_msg;
        $_SESSION['gerp_console']['messages'][] = $obj_msg;
    }

    /**
     * Clone di gerp_display_log
     *
     * @param $log_content
     * @param $params
     * @return false|void|null
     */
    public static function printLog($log_content)
    {
        global $obj_utente;

        if ( ! $obj_utente ) {
            return null;
        }

        if ( Gerp_Config::isErrorReportingConsole() ) {
            if ( is_array($log_content) ) {
                $log_content = print_r($log_content, true);
            }

            echo 'Log: ' . $log_content . "\n";
            return;
        }

        if ( ! $obj_utente->hasDebugMode() ) {
            return false;
        }

        if ( is_array($log_content) || is_object($log_content) ) {
            echo '<pre style="white-space: pre-wrap;">'. print_r($log_content, true) .'</pre>';
        } else {
            echo '<pre style="white-space: pre-wrap;">'. $log_content .'</pre>';
        }
    }

    public function fetch($clear_session = true)
    {
        if ( $clear_session ) {
            $_SESSION['gerp_console']['messages'] = [];
        }

        return self::$messages;
    }


    public static function display()
    {
        foreach ( self::$messages as $message ) {
            $message->display();
        }

        $_SESSION['gerp_console']['messages'] = [];
    }



}
