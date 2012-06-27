<?php

class Message
{
    /**
     * This function is used to set the reponse messages
     * that is used application wide like normal messages,
     * warnings, errors etc
     * @param string $status
     * @param string | array $message
     * @return string $_SESSION['message']
     */
    public static function setAppResponseMesseages ($status, $message)
    {
        $_SESSION['message'] = "";
        switch ($status) {
            case "error":
                $_SESSION['message'] = "<p class=\"error\">" . $message . "</p>";
                break;
            case "errors":
                $_SESSION['message'] = "";
                foreach ($message as $errorMessage) {
                    $_SESSION['message'] .= "<p class=\"error\">" . $errorMessage .
                     "</p>";
                }
                break;
            case "success":
                $_SESSION['message'] = "<p class=\"success\">" . $message .
                 "</p>";
                break;
            case "message":
                $_SESSION['message'] = "<p class=\"message\">" . $message .
                 "</p>";
                break;
            default:
                $_SESSION['message'] = "<p class=\"message\">" . $message .
                 "</p>";
        }
        return $_SESSION['message'];
    }

    /**
     * This function is to render the messages
     * stored in the $_SESSION['message'] variable
     * if not empty
     * @return string $output | boolean
     */
    public static function flashMessenger ()
    {
        $output = ! empty($_SESSION['message']) ? $_SESSION['message'] : "";
        unset($_SESSION['message']);
        return ! empty($output) ? $output : false;
    }
}