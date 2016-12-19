<?php

namespace DpDev\PhpEncoder;

class Encoder
{
	private $header_code = '';
	private $root_path = '';

	public function setFileHeaderCode($code)
	{
		$this->header_code = $code;
	}

	public function encodeFile($file)
	{
		$code = @php_strip_whitespace($file);
		$code_length = strlen($code);

		$namespace = '';
		if (preg_match('#namespace (.*?);#', $code, $m)) {
			$namespace = $m[0];
		}

		$rand = mt_rand(1000,9999) . mt_rand(1000,9999) . mt_rand(1000,9999);
		$file_hash = md5(str_replace(array("\r", "\n"), '', file_get_contents($file)));
		$this_key = md5($file_hash . $rand);

		$load_tpl = php_strip_whitespace(__DIR__.'/encode-tpl.php');
		$load_tpl = str_replace(array("\r", "\n"), '', $load_tpl);
		$load_tpl = str_replace('/*ENCODER_NAMESPACE*/', $namespace, $load_tpl);
		$load_tpl = trim(preg_replace("#^<\?php#", '', $load_tpl));
		$load_tpl = str_replace(
			array('__THISKEY__', '__ORIG_FILE_NAME__'),
			array($this_key, basename($file)),
			$load_tpl
		);

		$load_tpl = $this->obscureSymbols($load_tpl);

		$load_tpl = 'eval(base64_decode("' . base64_encode($load_tpl) . '"));';
		$load_hash = md5($load_tpl . $this_key);

		#------------------------------
		# "encode" the actual file
		#------------------------------

		$code_chunk_size = floor($code_length / 10);
		$code_salt = md5(uniqid());

		$new_code = base64_encode($code);
		$new_code = str_split($new_code, $code_chunk_size);

		// Add some fake chars that'll make normal base64_decode invalid
		$chars = '![]><@#_:?%!';
		foreach (array_keys($new_code) as $k) {
			$fake_chars1 = '';
			$fake_chars2 = '';
			for($i = 0; $i < mt_rand(0, 10); $i++) {
				$fake_chars1 .= $chars[mt_rand(0, 11)];
				$fake_chars2 .= $chars[mt_rand(0, 11)];
			}

			$new_code[$k] = $fake_chars1 . $new_code[$k] . $fake_chars2;
		}

		$new_code = implode('', $new_code);

		// Encode just a rnadom bit of it
		$take_size = min(mt_rand(250, 1500), $code_length);
		$start_pos = mt_rand(1, $code_length);

		$start = substr($new_code, 0, $start_pos);
		$start = strrev($start);

		$middle = substr($new_code, $start_pos, $take_size);
		$end = substr($new_code, $start_pos+$take_size);

		$middle = $this->xorString($middle, $this_key . $load_hash . $code_salt);
		$middle = base64_encode($middle);
		$middle_size = strlen($middle);

		// Some other fake substitutions
		$patterns = array();
		for ($i = 0; $i < 10; $i++) {
			do {
				$p = $this->random(mt_rand(4, 8));
			} while (strpos($p, $start) !== false OR strpos($p, $middle) !== false OR strpos($p, $end) !== false);
			$patterns[] = $p;
		}

		$patterns_str = implode(',', $patterns);

		$new_code = $start . '.' . $middle . '.' . $end;

		// Place them randomly throughout
		$pos = 0;
		$seg_size_min = 10;
		$seg_size_max = floor(strlen($new_code) / 20);
		while (true) {
			$pos = $pos + mt_rand($seg_size_min, $seg_size_max);
			if ($pos >= strlen($new_code)) break;
			if ($pos >= 65535) break;

			$new_code = preg_replace("#(?<=.{{$pos}})#", $patterns[array_rand($patterns)], $new_code, 1);
		}

		$new_code = chunk_split($new_code, 76, "\n");
		$code_hash = md5(trim(str_replace(array("\r", "\n"), '', $new_code)) . $this_key);

		$php = "<?php\n";
		if ($this->header_code) {
			$php .= $this->header_code . "\n\n";
		}
		$php .= "\n/***************************************************************************\n";
		$php .= $new_code;
		$php .= "****************************************************************************\n";
		$php .= base64_encode($this->xorString("$load_hash:$code_hash:$code_salt:$patterns_str", $load_hash.$code_hash));
		$php .= "\n***************************************************************************/\n";
		$php .= $load_tpl;
		$php .= "\n/**************************************************************************/\n";

		return $php;
	}

	/**
	 * This renames all the $__ variables in the loader to use
	 * obscurred numeric keys. So even if someone decodes
	 * it, its very hard to understand whats going on without good varnames.
	 *
	 * @param string $load_tpl
	 * @return string
	 */
	protected function obscureSymbols($load_tpl)
	{
		$counter = 6589;
		$map = array();

		$load_tpl = preg_replace_callback('#\$([a-zA-Z0-9_]+)#', function($m) use (&$counter, &$map) {
			$counter++;
			$old_name = $m[1];
			$new_name = $counter;

			if (isset($map[$old_name])) {
				$new_name = $map[$old_name];
			}

			$map[$old_name] = $new_name;

			return '$v' . $new_name;
		}, $load_tpl);

		return $load_tpl;
	}

	private function xorString($string, $key)
	{
		$string_len  = strlen($string);
		$key_len     = strlen($key);
		$new_string  = array();

		for ($i = 0, $j = 0; $i < $string_len; $i++, $j++) {
			if ($j >= $key_len) $j = 0;

			$new_string[] = chr(ord($string[$i]) ^ ord($key[$j]));
		}

		$new_string = implode('', $new_string);

		return $new_string;
	}

	private function random($len = 8, $chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz')
	{
		$string = '';
		$max_range = strlen($chars) - 1;

		for ($i = 0; $i < $len; $i++) {
			$string .= $chars[mt_rand(0, $max_range)];
		}

		return $string;
	}
}
