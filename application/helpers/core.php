<?php

/**
 * Core helper functions library
 * @package DTEch Framework
 * @author Eng. Mohammed Yehia
 * @copyright Dahab TEchnology 2011
 * @version 1 Friday 8th July 2011, 11:30 pm
 */

function __autoload($className)
{
	require_once $className . ".php";
}

/**
 * This function is used to redirect to a page
 * or a url based on the location parameter
 * @param string $location
 * @return void
 */

function redirectTo( $location = NULL )
{
	if ($location != NULL) {
		header("Location: {$location}");
		exit;
	}
}

/**
 * Renders any array
 * in a preformated typography
 * @param array $array
 */

function preFormatArray($array)
{
	echo "<pre>";
	print_r ( $array );
	echo "</pre>";
}

/**
 * This function is to render a new line
 * break to the page whenever its called
 * @return void
 */

function nl()
{
	echo "<br/>";
}

/**
 * This function is used to validate any form
 * fields based on 2 parameters a method that the
 * form is uses and an array of form fields with thier labels
 * to validate
 * @param string $formMethod
 * @param array $formFields
 * @throws Exception (Form fields must be an array)
 * @return boolean
 */

function validateFormFields($formMethod, $formFields)
{
	$errors = array();

	if(is_array($formFields)) {
		foreach ($formFields as $inputName => $label) {
			if($formMethod == "POST") {
				if(empty($_POST[$inputName])) {
					$errors[] = "Please fill in the " . $label;
				}
			} else {
				if(empty($_GET[$inputName])) {
					$errors[] = "Please fill in the " . $label;
				}
			}
		}
	} else {
		throw new Exception("Form fields must be an array.");
		return;
	}

	return !empty($errors) ? Message::setAppResponseMesseages("errors", $errors) : false;
}

/**
 * Return the execution time in milliseconds 
 * rounded up to 5 precisions
 * @param float $startTime
 * @param float $endTime
 */
function executionTime($startTime, $endTime)
{
    return round (($endTime - $startTime), 5);
}