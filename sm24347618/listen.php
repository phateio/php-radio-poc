<?php
/******************************************************************************
                                 PHP Radio POC
                                                    Req. PHP_VERSION >= '4.0.3'
*******************************************************************************/

ini_set('display_errors', 'Off');
ini_set('error_reporting', E_ALL);
ini_set('log_errors', 'On');

$is_gzip = isset($_SERVER['HTTP_ACCEPT_ENCODING']) && substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip');
if ($is_gzip) {
	ob_start('ob_gzhandler');
} else {
	ob_start();
}

ignore_user_abort(true);
ini_set('session.use_cookies', 'Off');
// exec('renice 19 '.getmypid());

header('Expires: Thu, 19 Nov 1981 08:52:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
header('Pragma: no-cache');

$bitrate = 24000;  // Bytes per second
$time_init = time();

// header('HTTP/1.0 200 OK');
header('Content-Type: audio/mpeg');
header('icy-br: 192');
header('ice-audio-info: ice-samplerate=48000;ice-bitrate=192;ice-channels=2');
header('icy-br: 192');
header('icy-description: PHP Radio POC v1.0');
header('icy-genre: Jpop');
header('icy-name: PHP Radio');
header('icy-private: 0');
header('icy-pub: 1');
header('icy-url: http://localhost/');
// header('Cache-Control: no-cache');

$mp3_path = 'sm24347618.mp3';
$mp3_data = file_get_contents($mp3_path);
$mp3_length = filesize($mp3_path);

while (true) {
	set_time_limit(900);

	$a = 0;
	$b = $a + $bitrate;
	$mp3_title = $offset = NULL;
	$time_start = time();
	$i = 1;	$m = 0; $cache = 4;

	for (;;) {
		if (connection_aborted())
			exit();
		if ($b > $mp3_length)
			$b = $mp3_length;
		$data = substr($mp3_data, $a, $b - $a).$offset;
		echo_block($data);
		ob_flush();
		if ($b == $mp3_length)
			break;
		$a += $bitrate;
		$b = $a + $bitrate;
		if ($i > $cache) {
			$time_wake_up = $time_start + $i;
			@sleep($time_wake_up - $m - time());
		} else {
			$time_wake_up = $time_start + $i;
			@sleep($time_wake_up - $m + 0.5 - time());
			++$m;
		}
		++$i;
	}
}

function echo_block($data) {
	$block_size = 240;
	$data_length = strlen($data);
	$a = 0;
	$b = $block_size;
	// $time_wake_up = time();
	for (;;) {
		if ($b > $data_length)
			$b = $data_length;
		$data_block = substr($data, $a, $b - $a);
		echo($data_block);
		ob_flush();
		if ($b == $data_length)
			break;
		$a += $block_size;
		$b = $a + $block_size;
		// $time_wake_up += 0.01;
		// @time_sleep_until($time_wake_up);
	}
}
