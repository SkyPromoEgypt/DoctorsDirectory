<?php

/**
 * Poll class extends DbAbstract class
 * @package DTEch Framework
 * @author Eng. Mohammed Yehia
 * @copyright Dahab TEchnology 2011
 * @version 1 Saturday 9th July 2011, 4:20 pm
 */
class Poll extends ModelAbstract implements IModel
{

    /**
     * Poll Object Id
     * @var integer
     * @access public
     */
    public $id;

    /**
     * Poll question extracted
     * from the qna array passed
     * to the makePoll method
     * @var string
     * @access public
     */
    public $question;

    /**
     * Poll answers
     * @var array
     * @access public
     */
    public $answers = array();

    /**
     * Poll votes
     * @var mixed array (associative)
     * @access public
     */
    public $votes = array();

    /**
     * The Class Database Table Name
     * @access protected
     * @var string
     */
    protected static $_tableName = "vote";

    /**
     * This property is used to be called in the abstract class
     * to define the class properties that is used to save records
     * to the database
     * @access protected
     * @var array
     */
    protected static $_dbFields = array('id', 'question', 'answers', 'votes');

    /**
     * Class Name to be called statically using late static binding
     * in the abstract class
     * @access protected
     * @var __CLASS__
     */
    protected static $_className = __CLASS__;

    public function __call ($name, $args)
    {
        $methodPrefix = substr($name, 0, 3);
        $methodProperty = strtolower($name[3]) . substr($name, 4);
        switch ($methodPrefix) {
            case "get":
                return $this->$methodProperty;
                break;
            case "set":
                if (count($args) == 1) {
                    $this->$methodProperty = $args[0];
                } else {
                    throw new \Exception(
                    "The Set method supports only 1 argument");
                }
                break;
            default:
                throw new \Exception("The method doesn't support this prefix");
                break;
        }
    }

    public function __toString ()
    {
        return $this->question;
    }

    public function __get ($classProperty)
    {
        return $this->$classProperty;
    }

    public function __set ($classProperty, $propertyValue)
    {
        $this->$classProperty = $propertyValue;
    }

    /**
     * Sets a cookie based on the
     * poll id
     * @param integer $id
     * @return void
     */
    private function _setCookie ($id)
    {
        $name = "poll_" . $id;
        // Set duration to 3 months from vote time
        $duration = time() + (60 * 60 * 24 * 7 * 3);
        setcookie($name, $id, $duration);
    }

    /**
     * Validates the Question and
     * Answers JSON String
     * @param JSON $qna
     * @return string $errors
     */
    private function _validateQna ($qna)
    {
        $errors = "";
        if (empty($qna)) {
            $errors = "You must type a question and its answers correctly using the guideline above.";
        } elseif (! $qna["question"]) {
            $errors = "Please provide a question to answer.";
        } elseif (! $qna["answers"]) {
            $errors = "Please provide some answers to the question.";
        } elseif (count($qna["answers"]) == 1) {
            $errors = "Please type more than 1 answer.";
        }
        return $errors;
    }

    /**
     * Creates a Poll Object and
     * save it to the Database...
     * @return boolean
     */
    public static function makePoll ()
    {
        if (isset($_POST['submit'])) {
            $qna = $_POST['qna'];
            $qna = json_decode($qna, true);
            $error = self::_validateQna($qna);
            $success = "Poll Created Successfully.";
            if ($error) {
                Message::setAppResponseMesseages("error", $error);
                return false;
            } else {
                $poll = new self();
                $poll->question = array_shift($qna);
                $poll->answers = json_encode($qna["answers"]);
                foreach ($qna["answers"] as $answer) {
                    $poll->votes[$answer] = 0;
                }
                $poll->votes = json_encode($poll->votes);
                if ($poll->save()) {
                    Message::setAppResponseMesseages("success", $success);
                    return true;
                }
            }
        }
    }

    /**
     * Votes for an answer
     * and sets cookie for the poll
     * using its Id then redirects to the page
     * to show results
     * @return void
     */
    private function _makeVote ()
    {
        if (isset($_POST['submit']) && isset($_POST['answer'])) {
            $sql = array("fields" => "*",
            "condition" => array("id" => $_POST['pid']), "limit" => 1);
            $poll = self::findInDb($sql);
            $answer = $_POST['answer'];
            $votes = json_decode($poll->votes, true);
            $votes[$answer] += 1;
            $poll->votes = json_encode($votes);
            $poll->save();
            $poll->_setCookie($_POST['pid']);
            redirectTo("index");
        }
    }

    /**
     * Renders a form to select the view of the
     * Poll's results wither horizontally or
     * vertically
     * @return string $output
     */
    private function _selectView ()
    {
        $output = '<form action="" method="post" id="viewSelection">
					<label for="view">Select a view</label><select name="view" id="view">
					<option value="0"';
        $output .= (isset($_POST['view']) && $_POST['view'] == "0") ? "selected=\"selected\"" : "";
        $output .= '>Horizontal</option>';
        $output .= '<option value="1"';
        $output .= (isset($_POST['view']) && $_POST['view'] == "1") ? "selected=\"selected\"" : "";
        $output .= '>Vertical</option>';
        $output .= '</select>&nbsp;&nbsp;<input type="submit" name="submit" id="submit" value="View Poll" class="submit" /></form>';
        return $output;
    }

    /**
     * Renders the Poll in its Form phase or
     * Results phase based on the cookie existance
     * @return string $output
     */
    public static function render ()
    {
        $output = "";
        $verticalView = isset($_POST['view']) ? $_POST['view'] : 0;
        $sql = array("fields" => "*", "orderBy" => "id", "order" => "DESC",
        "limit" => 1);
        $poll = self::findInDb($sql);
        $cookie = "poll_" . $poll->id;
        if (! $_COOKIE[$cookie]) {
            self::_makeVote();
            $output .= self::renderPollForVote($poll);
        } else {
            $output .= self::_selectView() .
             self::renderPollResults($poll, $verticalView);
        }
        return $output;
    }

    /**
     * Renders the Voting Form
     * @param self $poll
     * @return string $output
     */
    public static function renderPollForVote (self $poll)
    {
        if (! $poll) {
            $output = "No Polls have been created so far. Please create at least 1 Poll.";
        } else {
            $output = "";
            $question = $poll->question;
            $answers = json_decode($poll->answers);
            $output .= "<form action=\"\" method=\"post\" class=\"poll\">";
            $output .= "<h1>" . $question . "</h1><div class=\"answers\">";
            foreach ($answers as $answer) {
                $output .= "<p><input type=\"radio\" id=\"$answer\" name=\"answer\" value=\"$answer\" /> <label for=\"$answer\">$answer</label></p>";
            }
            $output .= "<input type=\"hidden\" name=\"pid\" value=\"$poll->id\" />";
            $output .= "<input type=\"submit\" name=\"submit\" value=\"Vote\" />";
            $output .= "</div></form>";
        }
        return $output;
    }

    /**
     * Renders the Poll Results
     * @param self $poll
     * @return string $output
     */
    public static function renderPollResults (self $poll, $verticalView = false)
    {
        if (! $poll) {
            $output = "No Polls have been created so far. Please create at least 1 Poll.";
        } else {
            $output = "";
            $question = $poll->question;
            $answers = json_decode($poll->answers);
            $votes = json_decode($poll->votes, true);
            $total = array_values($votes);
            $sum = 0;
            foreach ($total as $key => $value) {
                $sum += $value;
            }
            if (! $verticalView) {
                $output .= "<div class=\"poll\">";
                $output .= "<h1>" . $question . "</h1><div class=\"answers\">";
                foreach ($answers as $answer) {
                    $percent = ($votes[$answer] == 0) ? 0 : round(
                    ($votes[$answer] / $sum) * 100, 1);
                    $width = ($percent == 0) ? 0 : $percent * 250 / 100;
                    $output .= "<p>$answer</p><div class=\"vote\" style=\"width: " .
                     $width . "px;\">$percent</div><div class=\"hr\"></div>";
                }
                $output .= "</div></div>";
            } else {
                $output .= "<div id=\"statistics\"><div class=\"virticalAnswers\">";
                foreach ($answers as $answer) {
                    $percent = ($votes[$answer] == 0) ? 0 : round(
                    ($votes[$answer] / $sum) * 100, 1);
                    $height = ($percent == 0) ? 0 : $percent * 280 / 100;
                    $output .= "<div class=\"verticalVote\" style=\"height: " .
                     $height . "px;\">$percent</div>";
                }
                $output .= "<div id=\"pollDetails\"><h1>" . $question .
                 "</h1><ul class=\"answersList\">";
                foreach ($answers as $answer) {
                    $percent = ($votes[$answer] == 0) ? 0 : round(
                    ($votes[$answer] / $sum) * 100, 1);
                    $height = ($percent == 0) ? 0 : $percent * 280 / 100;
                    $output .= "<li>$answer ($percent %)</li>";
                }
                $output .= "</ul></div></div></div>";
            }
        }
        return $output;
    }
}