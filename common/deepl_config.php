<?php

// DeepL config
// This file maps between different abbreviations for the various languages
// and sets our default language, and language sets
// as well as the location of the langauge files ($Smarty->config_dir)
// The DeepL API key is stored in a separate file elsewhere

// DeepL supported languages, using their language codes
define('DEEPL_LANGUAGES', array( 'bg','cs','da','de','el','en-gb','en-us','es','et','fi','fr','hu','it','ja','lt','lv','nl','pl','pt-br','pt-pt','ro','ru','sl','sv','zh' )) ;

// Exclude translations - this stops our tool translating between variants of English
// which might be amusing, but since it costs money, nah!
define('DEEPL_EXCLUDE', array( 'en-gb', 'en-us' )) ;

// DeepL supports the informal tone for some languages; these are the one where we want to use it
define('DEEPL_INFORMAL', array('de','fr','it','es','nl','pl','pt-pt','pt-br','ru' ));

// These are the language codes we want to support (using config file names)
// It's the set that deepler will use when we ask for 'all languages'
define('DEEPL_LANG_CONFIGS', array( 'bg','cz','dk','de','gr','en','es','ee','fi','fr','hu','it','jp','lt','lv','nl','pl','br','pt','ro','ru','se','cn' )) ;

// This is our core set of languages, typically all the ones your web site uses
define('DEEPL_LANG_CORE', array( 'en', 'de', 'fr', 'es' )) ;

// This is the extra messages that make up our extended set
define('DEEPL_LANG_EXT', array( 'it', 'br' )) ;

// This is the set we use for testing
define('DEEPL_LANG_TEST', array( 'en', 'de' )) ;

// And this is our base language
define('DEEPL_BASE', 'en') ;

// Where the language files are stored on the server
define('LANGUAGE_DIR', '/var/bluf/site/language/') ;

define('DEEPL_ERRORS_TO', 'nigel@nigelwhitfield.com') ;

// ISO country name mapping to language codes
// because in some places, it's easier for people to remember the former
define('DEEPL_ISO_INPUT', array(
	'br' => 'pt-br',
	'cn' => 'zh',
	'cz' => 'cs',
	'dk' => 'da',
	'ee' => 'et',
	'gb' => 'en-gb',
	'gr' => 'el',
	'jp' => 'ja',
	'pt' => 'pt-pt',
	'se' => 'sv',
	'us' => 'en-us'
)) ;

// Maps from section codes in our Smarty config files to DeepL language codes
define('DEEPL_FROM_LANGUAGE', array(
	'br' => 'pt-br',
	'cn' => 'zh',
	'cz' => 'cs',
	'en' => 'en-gb', // you might prefer en-us if you're American
	'ee' => 'et',
	'gr' => 'el',
	'dk' => 'da',
	'jp' => 'ja',
	'pt' => 'pt-pt',
	'se' => 'sv',
)) ;

// Maps from DeepL codes to the section codes we use in our Smarty config files
define('DEEPL_TO_LANGUAGE', array(
	'cs' => 'cz',
	'da' => 'dk',
	'el' => 'gr',
	'en-gb' => 'en',
	'et' => 'ee',
	'ja' => 'jp',
	'pt-br' => 'br',
	'pt-pt' => 'pt',
	'sv' => 'se',
	'zh' => 'cn'
)) ;
