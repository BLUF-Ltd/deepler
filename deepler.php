<?php

// BLUF 4.5 DeepLer

// Takes a text file (found in the languages folder) as a param, and
// extracts English section, then translates via DeepL to generate a new version

// default action: skip a file that's already marked up
// assume english, no headers, and create core languages
//
// options:
// 	--append : add missing languages to an existing file
//	--extended : use extended language set
//	--all : use all languages
//	--rebuild : regenerate all languages from English
//	--help : display help message

// Version 1.5
// Date: 2023-04-15
// Used new constant declarations
// First public commit

require('common/deepl.php') ;

setlocale(LC_ALL, 'en_GB') ;

if (in_array('--help', $argv)) {
	// display help and exit
	print "deepler 1.5
Usage: deepler.php <files>
All .txt files will be translated from English to core languages
Options:
	--extended	use extended language set, instead of core
	--all		use all languages, instead of core
	--append	add missing langauges, instead of skipping files with markup
	--rebuild	regenerate all languages in a file, from English
	--test		use test language set
	--help		display this text\n" ;

	exit ;
}

// see what command params we have
// build our set of languages
$language_set = in_array('--extended', $argv) ? array_merge(DEEPL_LANG_CORE, DEEPL_LANG_EXT) : DEEPL_LANG_CORE ;
$language_set = in_array('--all', $argv) ? DEEPL_LANG_CONFIGS : $language_set ;
$language_set = in_array('--test', $argv) ? DEEPL_LANG_TEST : $language_set ;


$rebuild = in_array('--rebuild', $argv) ;
$append = in_array('--append', $argv) ;

printf("Language set = %s\n", implode(' ', $language_set)) ;

// if a param name ends in .txt and is in the language directory, it's a file to translate
foreach ($argv as $arg) {
	if (preg_match('/\.txt/', $arg)) {
		if (file_exists(LANGUAGE_DIR . $arg) && is_readable(LANGUAGE_DIR . $arg)) {
			printf("Opening file %s\n", $arg) ;

			$usage = deepl_usage() ;
			if ($usage === false) {
				die("DeepL error. Stopping ... \n");
			}
			printf("Current usage = %d (%2.2f%%)\n", $usage['character_count'], ($usage['character_count']/$usage['character_limit'])*100) ;

			$file_as_string = file_get_contents(LANGUAGE_DIR . $arg) ;
			$file_languages = detect_language_markup($file_as_string) ;

			if (count($file_languages) > 0) {
				printf("File contains %s\n", implode(' ', $file_languages)) ;
			}

			// this is our output file
			$output = array() ;

			if ($file_languages) {
				if ($rebuild  ||  $append) {
					// we need to get the base text
					$basetext = get_language_section(DEEPL_BASE, $file_as_string) ;

					printf("Loaded file, %d lines of base text\n", count($basetext)) ;

					// we also want to get any header section (ie blank lines, or lines beginning with # up to the first other line )
					$header = file(LANGUAGE_DIR . $arg) ;
					$headingsent = false ;
					foreach ($header as $line) {
						if ($headingsent) {
							break ;
						}
						if (trim($line == '') || (preg_match('/^#/', $line))) {
							$output[] = $line ;
						} else {
							if (! $headingsent) {
								$output[] = "#\n# Updated by DeepLer, " . strftime('%c') . "\n#\n" ;
								$output[] = "\n[" . DEEPL_BASE . "]\n" ;
								$headingsent = true ;
							}
						}
					}

					// now append the base text to the output
					foreach ($basetext as $line) {
						if (trim($line) != '') {
							$output[] = $line . "\n" ;
						}
					}
					$output[] = "\n" ;

					// now iterate over the languages
					foreach ($language_set as $lang) {
						if ($lang == DEEPL_BASE) {
							continue ;
						}

						// always output the correct codes, and use the correct DeepL code
						$olang = (isset(DEEPL_TO_LANGUAGE[$lang])) ? DEEPL_TO_LANGUAGE[$lang] : $lang ;
						$tlang = (isset(DEEPL_FROM_LANGUAGE[$lang])) ? DEEPL_FROM_LANGUAGE[$lang] : $lang ;

						if ((!$rebuild) && in_array($olang, $file_languages)) {
							// add the existing section to the output
							print(" ... reusing ($lang)\n") ;
							$output[] = "\n\n[$olang]\n" ;
							foreach (get_language_section($olang, $file_as_string) as $line) {
								if (trim($line) != '') {
									$output[] = $line . "\n" ;
								}
							}
						} else {
							// don't translate certain things
							if (in_array($tlang, DEEPL_EXCLUDE)) {
								continue ;
							}
							print(" ... working ($lang)\n") ;
							$output[] = "\n\n[$olang]\n" ;
							foreach ($basetext as $line) {
								if (preg_match('/^(.*)\s*=\s+(.*)/', $line, $matches)) {
									// we have an item = text entry, so translate the item
									$output[] = $matches[1] . '= ' . deepl_translation($matches[2], $tlang) . "\n";
								}
							}
						}
					}
				} else {
					printf("File is already multilingual.... skipping (use --append or --rebuild)\n") ;
					continue ;
				}
			} else {
				// we assume this is just the base version, so read into an array
				$basetext = file(LANGUAGE_DIR . $arg) ;

				// make the english section of the output
				// output blank lines and comments until we find first actual entry,
				// then output the [en] heading

				$headingsent = false ;

				foreach ($basetext as $line) {
					if (trim($line == '') || (preg_match('/^#/', $line))) {
						$output[] = $line ;
					} else {
						if (! $headingsent) {
							$output[] = "#\n# Generated by DeepLer, " . strftime('%c') . "\n#\n" ;
							$output[] = "\n[" . DEEPL_BASE . "]" ;
							$headingsent = true ;
						}
						$output[] = $line ;
					}
				}


				foreach ($language_set as $lang) {
					if ($lang == DEEPL_BASE) {
						continue ;
					}

					print(" ... working ($lang)\n") ;

					// always output the correct codes, and use the correct DeepL code
					$olang = (isset(DEEPL_TO_LANGUAGE[$lang])) ? DEEPL_TO_LANGUAGE[$lang] : $lang ;
					$tlang = (isset(DEEPL_FROM_LANGUAGE[$lang])) ? DEEPL_FROM_LANGUAGE[$lang] : $lang ;

					// don't translate certain things
					if (in_array($tlang, DEEPL_EXCLUDE)) {
						continue ;
					}

					$output[] = "\n\n[$olang]\n" ;

					foreach ($basetext as $line) {
						if (trim($line) == '') {
							continue ;
						}
						if (preg_match('/^#/', $line)) {
							continue ;
						}

						if (preg_match('/^(.*)\s*=\s+(.*)/', $line, $matches)) {
							// we have an item = text entry, so translate the item
							$output[] = $matches[1] . '= ' . deepl_translation($matches[2], $tlang) . "\n";
						}
					}
				}
			}
			// $output now contains the file to rewrite
			$archivename = preg_replace('/\.txt/', '-' . time() . '.txt', $arg) ;
			rename(LANGUAGE_DIR . $arg, LANGUAGE_DIR . $archivename) ;
			file_put_contents(LANGUAGE_DIR . $arg, $output) ;

			$usage = deepl_usage() ;
			if ($usage === false) {
				die("DeepL error. Stopping ... \n");
			}
			printf("Original archived as %s, usage now (%2.2f%%)\n", $archivename, ($usage['character_count']/$usage['character_limit'])*100) ;
		}
	}
}
