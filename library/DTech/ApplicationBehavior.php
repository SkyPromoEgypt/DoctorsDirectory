<?php

class ApplicationBehavior extends ModelAbstract
{

    function __construct ()
    {}

    public static function checkSqlStatementArray ()
    {
        if (! empty(self::$_sqlStatements)) {
            return self::queryForReview();
        }
    }

    public static function showSqlWindow()
    {
        $div = new HtmlPage();
        $sqlLink = $div->addElement("a", "<b>SQL Windows</b>", array("href" => "#"));
        $sqlWindow = $div->addElement("div", array($sqlLink,
        "<form action=\"\" method=\"post\" class=\"form\">
        <h1>Sql Query Windows</h1>
        <p>Type your sql query here and press execute to fetch results from the database.</p>
        <table width=\"100%\">
        <tr><td width=\"200\" valign=\"top\" align=\"left\" colspan=\"2\"></td></tr>
        <tr><td colspan=\"2\"><textarea name=\"sqlquery\" id=\"sqlquery\"></textarea></td></tr>
        <tr><td width=\"80\" valign=\"middle\" align=\"left\">
        <input type=\"submit\" name=\"excutesql\" id=\"excutesql\" value=\"Excute Query\" class=\"submit\" />
        </td><td valign=\"middle\" align=\"left\"></td></tr>
        </table>
        </form>
        <div id=\"sqlResults\" class=\"form2\"></div>"
        ), array("id" => "sqlWindow"));
        return $sqlWindow;
    }
}