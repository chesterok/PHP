#!c:/xampp/php/php
<?php
if ( count($argv) < 2 ) {
	echo 'Usage: ' . $argv[0] .  ' ' . '[url]' . PHP_EOL;
} else {
	$url = $argv[1];

	if ( filter_var($url, FILTER_VALIDATE_URL) ) {
		$urlAnswer = get_headers($url)[0];

		if ( preg_match('#^HTTP/1\.[01] (?:2\d\d|3\d\d)#', $urlAnswer) ) {
			$file = basename($url);

			file_put_contents($file, file_get_contents($url));
		} else {
			echo $urlAnswer . PHP_EOL;
		}
	} else {
		echo "Incorrect [url]" . PHP_EOL;
	}
}
?>