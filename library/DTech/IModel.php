<?php

interface IModel {
	public function __call($name, $args);
	public function __toString();
	public function __get($classProperty);
	public function __set($classProperty, $propertyValue);
}