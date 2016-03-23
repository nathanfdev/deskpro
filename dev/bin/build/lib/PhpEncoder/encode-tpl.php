<?php

$filecontent = file_get_contents(preg_replace('#\.php.*?$#', '.php', __FILE__));

$fail_fn = function($c) {
	if (php_sapi_name() !== 'cli') {
		@header("HTTP/1.0 520 Unknown Error");
		@header('Content-Type: text/plain');
	}
	echo "Invalid file detected: __ORIG_FILE_NAME__\n";
	echo "Please re-download the DeskPRO distribution.\n\n";
	echo "(Code:$c)\n";
};

#----------------------------------------
# Get the parts of the file
#----------------------------------------

$file_split = preg_split(
	'#(/\*{75}|\*{76}|\*{75}/|/\*{74}/)#',
	$filecontent
);

if (!$file_split || count($file_split) != 5) {
	$fail_fn('1001:'.count($file_split));
	exit(1);
}

$file_code = $file_split[1];
$hash_info = $file_split[2];
$load_code = $file_split[3];

$nl_array = ["\r", "\n"];
$load_hash = md5(trim(str_replace($nl_array, '', $load_code)) . '__THISKEY__');
$code_hash = md5(trim(str_replace($nl_array, '', $file_code)) . '__THISKEY__');

unset($file_split, $filecontent);

#----------------------------------------
# Get hashes and verify
#----------------------------------------

$hash_info         = base64_decode($hash_info);
$hash_info_len     = strlen($hash_info);
$hash_info_key     = $load_hash . $code_hash;
$hash_info_key_len = strlen($hash_info_key);

// XOR to get real string
$hash_info_new = '';
for ($i = 0, $j = 0; $i < $hash_info_len; $i++, $j++) {
	if ($j >= $hash_info_key_len) $j = 0;

	$hash_info_new .= chr(ord($hash_info[$i]) ^ ord($hash_info_key[$j]));
}

$hash_info = explode(':', $hash_info_new);

unset(
	$hash_info_len,
	$hash_info_xor_key,
	$hash_info_xor_key_len,
	$hash_info_new,
	$i,
	$j
);

if ($hash_info[0] != $load_hash) {
	$fail_fn('2001');
	exit(2);
}
if ($hash_info[1] != $code_hash) {
	$fail_fn('3001');
	exit(3);
}

$code_salt = $hash_info[2];
$fake_patterns = explode(',', $hash_info[3]);

#----------------------------------------
# Reverse
#----------------------------------------

// Remove the chunking
$file_code = str_replace($nl_array, '', $file_code);

// Remove invalid chars
$file_code = str_replace(str_split('![]><@#_:?%'), '', $file_code);

// Remove fake char sequences
$file_code = str_replace($fake_patterns, '', $file_code);

// Split into the three parts
$file_code = explode('.', $file_code, 3);

// First segment is reversed, so put it back
$file_code[0] = strrev($file_code[0]);

// Second segment is XOR'ed
$file_code[1]       = base64_decode($file_code[1]);
$file_code1_len     = strlen($file_code[1]);
$file_code1_key     = '__THISKEY__' . $load_hash . $code_salt;
$file_code1_key_len = strlen($file_code1_key);

// XOR to get real string
$file_code1_new = array();
for ($i = 0, $j = 0; $i < $file_code1_len; $i++, $j++) {
	if ($j >= $file_code1_key_len) $j = 0;

	$file_code1_new[] = chr(ord($file_code[1][$i]) ^ ord($file_code1_key[$j]));
}
$file_code[1] = implode('', $file_code1_new);

unset(
	$file_code1_len,
	$file_code1_key,
	$file_code1_key_len,
	$file_code1_new,
	$i,
	$j,
	$fail_fn
);

// Put the pieces back together
$file_code = implode('', $file_code);
$file_code = base64_decode($file_code);

eval('/*ENCODER_NAMESPACE*/ ?>'.$file_code);
unset($file_code);
