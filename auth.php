<?php
if (!isset($_SERVER['PHP_AUTH_USER']) || $_SERVER['PHP_AUTH_PW'] !== $password) {
	header('WWW-Authenticate: Basic realm="books"');
	header('HTTP/1.0 401 Unauthorized');
	echo 'hello world';
	exit;
}

