<?php

class Base {
	const DIRECTORY_PATH_MODIFICATOR = '..' . DIRECTORY_SEPARATOR;

	protected function pathMe() {
		if (basename($_SERVER["SCRIPT_FILENAME"]) == "admin.php") {
			return self::DIRECTORY_PATH_MODIFICATOR;
		} else {
			return "";
		}
	}
}