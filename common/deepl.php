<?php

// BLUF 4.5 DeepL translation functions
// moved from admin translate

// Version 1.5
// Date: 2023-04-15
// Switch to using new configs
// First public commit

// To use this code, you need the KEYSdeepl file, which contains the API key:

require_once('KEYSdeepl.php') ; // contains the API key as KEY_DEEPL
require_once('deepl_config.php') ;


function deepl_translation(string $original, string $to_lang, bool $autodetect = false): string
{
	// function to return a DeepL translation for the original text
	// If autodetect is passed, let DeepL guess
	// Otherwise assume base, and check the formality setting

	// Typically, we use autodetect for user messages

	if (! in_array($to_lang, DEEPL_LANGUAGES)) {
		// not using a DeepL code, try the iso country mapping
		$to_lang = DEEPL_ISO_INPUT[$to_lang] ;
	}

	$curl = curl_init();

	$endpoint = 'https://api.deepl.com/v2/translate?auth_key=' . KEY_DEEPL ;

	if ($autodetect) {
		$params = array( 'auth_key' => KEY_DEEPL, 'target_lang' => strtoupper($to_lang), 'text' => $original) ;
	} else {
		if (in_array($to_lang, DEEPL_INFORMAL)) {
			$params = array( 'auth_key' => KEY_DEEPL, 'source_lang' => strtoupper(DEEPL_BASE), 'target_lang' => strtoupper($to_lang), 'formality' => 'less', 'text' => $original) ;
		} else {
			$params = array( 'auth_key' => KEY_DEEPL, 'source_lang' => strtoupper(DEEPL_BASE), 'target_lang' => strtoupper($to_lang), 'text' => $original) ;
		}
	}



	curl_setopt_array(
		$curl,
		array(
		CURLOPT_URL => $endpoint,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => "",
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 30,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_POSTFIELDS => $params
		)
	);

	$response = curl_exec($curl);

	$err = curl_error($curl);

	$rcode = curl_getinfo($curl, CURLINFO_RESPONSE_CODE) ;

	curl_close($curl);

	if (($err) || ($rcode >= 400)) {
		mail(DEEPL_ERRORS_TO, 'Deepl error ' . $rcode, $err . print_r($params, true)) ;
		return $rcode ;
	} else {
		$result = json_decode($response, true) ;

		openlog('DEEPL', LOG_ODELAY, LOG_LOCAL6) ;
		$logmsg = sprintf('%04d chars translated from %s to %s, auto = %s', strlen($original), $result['translations'][0]['detected_source_language'], strtoupper($to_lang), $autodetect) ;
		syslog(LOG_NOTICE, $logmsg) ;


		return $result['translations'][0]['text'] ;
	}
}

function deepl_usage()
{
	// check current usage of DeepL
	// Returns either an array of usage info, or false

	$curl = curl_init();

	$endpoint = 'https://api.deepl.com/v2/usage?auth_key=' . KEY_DEEPL ;

	curl_setopt_array(
		$curl,
		array(
		CURLOPT_URL => $endpoint,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => "",
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 30,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		)
	);

	$response = curl_exec($curl);
	$result = json_decode($response, true) ;

	$err = curl_error($curl);

	curl_close($curl) ;

	if ($err) {
		return false ;
	} else {
		return $result ;
	}
}

function detect_language_markup(string $text): array
{
	// return an array of the languages found in a config file
	// open the config file with file_get_contents
	preg_match_all('/\[(' . implode('|', DEEPL_LANG_CONFIGS) . ')\]/', $text, $results, PREG_PATTERN_ORDER) ;

	return $results[1] ;
}

function get_language_section(string $lang, string $text): array
{
	// extract a specified language section from a string
	// eg a file opened with file_get_contents
	// return an array of lines representing the section requested

	$tagfound = false ;
	$tagmatch = false ;
	$revert = false ;
	$result = '' ;


	$languages = preg_split('/\[(' . implode('|', DEEPL_LANG_CONFIGS) . ')\]/', $text, null, PREG_SPLIT_DELIM_CAPTURE) ;

	foreach ($languages as $l) {
		if (preg_match('/(' . implode('|', DEEPL_LANG_CONFIGS) . ')/', $l) && (strlen($l) == 2)) {
			$tagfound = true ;
		}

		if (($l == $lang) && ($tagfound)) {
			$tagmatch = true ;
			continue ;
		}

		if (($tagfound && $tagmatch)) {
			$result .= $l ;

			// in case there are other instances
			$tagfound = false ;
			$tagmatch = false ;
		}
	}

	return preg_split('/\n/', $result, null, PREG_SPLIT_DELIM_CAPTURE) ;
}
