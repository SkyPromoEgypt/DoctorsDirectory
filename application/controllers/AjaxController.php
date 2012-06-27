<?php

class AjaxController extends FrontController
{

    public function GetSqlAction ()
    {
        $db = Database::getInstance();
        $sql = $_GET['sql'];
        $startTime = microtime(true);
        $resultsArray = $db->query($sql);
        $endTime = microtime(true);
        
        $results = array();
        for ($i = 0; $row = $db->fetchArray($resultsArray, MYSQL_ASSOC); $i ++) {
            $results[] = $row;
        }
        $output = "<p>Returned " . $db->numRows($resultsArray) . " rows in " . executionTime($startTime, $endTime) . " sec.</p>";
        $output .= "<table class=\"tableControl\"><tr>";
        $firstRowData = $results[0];
        foreach ($firstRowData as $tableHeader => $value) {
            $output .= "<th>$tableHeader</th>";
        }
        $output .= "</tr>";
        foreach ($results as $result) {
            $output .= "<tr>";
            foreach ($result as $field => $fieldValue) {
                $output .= "<td>$fieldValue</td>";
            }
            $output .= "</tr>";
        }
        $output .= "</table>";
        header('Content-Type:text/html');
        echo $output;
    }
}