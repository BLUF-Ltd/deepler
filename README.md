# deepler
A tool to automate translation of language files for our Smarty-based website

The [BLUF website](https://www.bluf.com/) is avaiable in multiple languages. Our core site is in English, German, French and Spanish,
while our [signup microsite](https://join.bluf.com) additionally includes Italian and Brasilian Portuguese.

Pages for the site are generated using the [Smarty PHP template engine](https://github.com/smarty-php) and we use Smarty config files
to store the multilingual texts for each page.

That means that for each page or section of the site, a text file maps Smarty constants, loaded by the template, to the actual text, like this

    # This is a single language config file
    pagetitle     = Welcome to BLUF
    heading       = Welcome to the online home of BLUF, the club for leather men
    
The corresponding Smarty template will look something like this

    {config_load file='welcome.txt' section=$language}
    <head>
      <title>{#pagetitle#}</title>
    </head>
    <body>
      <h1>{#heading#}</h1>
      
When developing the page, we create a simple config file, and the English language text is used, regardless of the specified language.

### Multilingual pages
To make a page multilingual, all we need to do is to divide the config file into sections, each one named for a value of the $language variable,
like this

    # BLUF multi-language config file
    #
  
    [en]
    pagetitle     = Welcome to BLUF
    heading       = Welcome to the online home of BLUF, the club for leather men
  
    [de]
    pagetitle     = Willkommen bei BLUF
    heading       = Willkommen auf der Online-Seite von BLUF, dem Club für Ledermänner
  
    [fr]
    pagetitle     = Bienvenue à BLUF
    heading       = Bienvenue sur le site de BLUF, le club des hommes en cuir.
  
So, once we've debugged a page, and created the english version of all the texts - which can include variables set via the Smarty engine -
all we need to do is to add the extra langauges to the config file, and we have a multilingual version.

In the past we used a team of volunteer translators, and you'll probably get the best results by using real native speakers. But that tends
to either cost money, or take time. As we now try to ensure that when a feature is added to the site, it's immediately available to users
regardless of langauge, there have been times where the rollout has been delayed significantly not by testing, but by waiting for all the
languages to be translated.


### Machine translation
Machine translation is nothing new, but having used Google translate in the past when we needed something quickly, we've not been happy with
the results, and they've often required tweaking afterwards by a real person.

However, the [DeepL](https://www.deepl.com/translator) translator is much better regarded, and it provides an API that can be accessed for
pretty reasonable fees.

So, we have created a small tool, which we call deepler, that can be given the names of one or more of our Smarty config files, and uses the
DeepL API to create the translated version.

Now, when we're ready to go with a new page or feature, a simple command like

    php deepler.php demo.txt
    
is all that we need to turn a single language config file into a multilingual one, with the added section headers.

### deepler options
Because sometimes we do go back and change things, we've added a few extra options; we try not to over-write existing translations, in case
they have been tweaked by a real person. But we can force the tool to rebuild everything from English, or just to append any languages that
are missing.

The deepl_arrays.php file configures things like the core set of languages (for us, English, German, French, Spanish), an extended set, a smaller
test set, languages for which we prefer to use the informal option, if avaiable. It also maps between an ISO set of codes and those
used internally by DeepL, where required.

    Usage: deepler.php <files>
    All .txt files will be translated from English to core languages
    Options:
    	--extended	use extended language set, instead of core
    	--all		use all languages, instead of core
    	--append	add missing langauges, instead of skipping files with markup
    	--rebuild	regenerate all languages in a file, from English
    	--test		use test language set
    	--help		display this text
      
When run, deepl will display what it's doing, which language it's working on (or skipping, if already present), and then at the end report
the current usage and quota % from the DeepL account.

We make no great claims for the quality of this code; it's simply a tool that helps us get a job done fast - converting our website into
multiple languages, with a fair degree of accuracy, and minimal work.
