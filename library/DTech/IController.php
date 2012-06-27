<?php

interface IController {
	public function dispatch();
	public function render($pageTitle);
	public function getViewFolder();
}