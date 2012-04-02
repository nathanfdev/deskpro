<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
* DeskPRO
*
* @package DeskPRO
*/

namespace Application\DevBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Application\DeskPRO\App;

use Orb\Util\Strings;
use Orb\Util\Arrays;

class LanguageController extends Controller
{
    public function indexAction()
    {
		return $this->render('DevBundle:Language:index.html.twig');
    }

    public function listLanguageFilesAction()
    {
		return $this->render('DevBundle:Language:simplelist.html.twig', array('list'=>$this->getLanguageFileList()));
    }

    public function listPhpFilesAction()
    {
		return $this->render('DevBundle:Language:simplelist.html.twig', array('list'=>$this->getPhpFileList()));
    }

    public function listTwigFilesAction()
    {
		return $this->render('DevBundle:Language:simplelist.html.twig', array('list'=>$this->getTwigFileList()));
    }

    public function testLexerAction()
    {
        $lexer = $this->getTwigLexer(array());
        $templates = $this->getTwigFileList();
        $mismatches = array();

        foreach($templates as $file) {
            $raw_twig = file_get_contents($file);
            $tokens = $lexer->tokenize($raw_twig);
            $new_raw_twig = $this->twigTokensToString($tokens);

            if($raw_twig != $new_raw_twig) {
                file_put_contents('./tmp', $new_raw_twig);
                $output = shell_exec('diff -w '.escapeshellarg($file).' ./tmp');;
                unlink('./tmp');
                $mismatches[] = array('filename' => $file, 'diff' => $output);
            }

            if(count($mismatches)>10)
                break;
        }

        $vars = array('mismatches' => $mismatches);
        return $this->render('DevBundle:Language:test.lexer.html.twig', $vars);
    }

    public function findRawStringsAction($bundle)
    {
        $twig_options = array();
        $lexer = $this->getTwigLexer($twig_options);
        $templates = $this->getTwigFileList($bundle);
        $untranslated = array();

        function get_strings($element, &$strings)
        {
            $children = $element->childNodes;

            if(!$children) {
                return;
            }

            if(in_array($element->tagName, array('script', 'style'))) {
                return;
            }

            foreach($children as $child) {
                if($child->nodeType == XML_TEXT_NODE) {
                    $string = $child->textContent;

                    if(preg_match('/^\s*$/', $string)) {
                        continue;
                    }

                    $string = preg_replace('/^\s*/', '', $string);
                    $string = preg_replace('/\s*$/', '', $string);
                    $string = preg_replace('/:$/', '', $string);
                    $string = preg_replace('/ \*$/', '', $string);

                    if($string == '' || strlen($string) == 1) {
                        continue;
                    }

                    if(!preg_match('/[a-zA-Z]/', $string)) {
                        continue;
                    }

                    $strings[] = $string;
                }
                else if($child->nodeType == XML_ELEMENT_NODE) {
                    get_strings($child, $strings);
                }
                else if($child->nodeType == XML_COMMENT_NODE) {
                }
                else if($child->nodeType == XML_ENTITY_REF_NODE) {
                }
                else if($child->nodeType == XML_PI_NODE) {
                }
                else {
                    throw new \Exception('Bad child type ('.$child->nodeType.').');
                }
            }
        }

        foreach($templates as $file) {
            $raw_twig = file_get_contents($file);
            $tokens = $lexer->tokenize($raw_twig);
            $html = $this->stripTwig($tokens);
            $config = array(
           'indent'         => false,
           'output-xhtml'   => true);


            $tidy = new \tidy;
            $tidy->parseString($html, $config, 'utf8');
            $tidy->cleanRepair();

            try {
                $document = new \DOMDocument();
                @$document->loadXML($tidy);
                $element = $document->documentElement;

                if(!$element) {
                    throw new \Exception();
                }
            }
            catch(\Exception $e) {
                echo "Warning: Could not parse {$file}!<br />";
                continue;
            }

            $strings = array();
            get_strings($element, $strings);

            if(count($strings)) {
                $untranslated[$file] = $strings;
            }

            unset($strings);
        }

        $vars = array('untranslated' => $untranslated);

        return $this->render('DevBundle:Language:find.raw.html.twig', $vars);
    }

    public function findPhrasesInTwigFilesAction()
    {
        $twig = $this->container->get('twig');
        $templates = $this->getTwigFileList();
        $vars = array(
            'instances' => array
            (
                'id' => array(),
                'file' => array()
            ),
            'missing' => array()
        );

        foreach($templates as $file) {
            $raw_twig = file_get_contents($file);
            $tokens = $twig->tokenize($raw_twig);
            $state = 0;
            $line = 0;

            while(!$tokens->isEOF()) {
                $token = $tokens->next();
                $type = $token->getType();
                $value = $token->getValue();
                $line = $token->getLine();

                switch($state) {
                    case 0:
                        if($type == \Twig_Token::NAME_TYPE && $value == 'phrase') {
                            $state++;
                        }

                        break;
                    case 1:
                        if($type == \Twig_Token::PUNCTUATION_TYPE && $value == '(') {
                            $state++;
                        }
                        else {
                            $state = 0;
                            $this->tokenWarningTwig('Unexpected token', $token, $file, $line);
                        }

                        break;
                    case 2:
                        if($type == \Twig_Token::STRING_TYPE) {
                            $state++;
                            $id = $value;

                            if(!isset($vars['instances']['id'][$id])) {
                                $vars['instances']['id'][$id] = array();
                            }

                            $vars['instances']['id'][$id][] = array(
                                'filename' => $file,
                                'line' => $line
                            );

                            if(!isset($vars['instances']['file'][$file])) {
                                $vars['instances']['file'][$file] = array();
                            }

                            $vars['instances']['file'][$file][] = array(
                                'id' => $id,
                                'line' => $line
                            );
                        }
                        else {
                            $state = 0;
                            $this->tokenWarningTwig('Unexpected token', $token, $file, $line);
                        }

                        break;
                    case 3:
                        if($type != \Twig_Token::PUNCTUATION_TYPE || ($value != ')' && $value != ',')) {
                            $this->tokenWarningTwig('Unexpected token', $token, $file, $line);
                        }

                        $state = 0;

                        break;
                }
            }
        }

        $vars['missing'] = $this->getMissing(array_keys($vars['instances']['id']));

        return $this->render('DevBundle:Language:find.phrases.twig.html.twig', $vars);
    }

    public function findPhrasesInPHPFilesAction()
    {
        $files = $this->getPhpFileList();
        $vars = array(
            'instances' => array
            (
                'id' => array(),
                'file' => array()
            ),
            'missing' => array()
        );

        foreach($files as $file) {
            $rawphp = file_get_contents($file);
            $tokens = token_get_all($rawphp);
            $state = 0;
            $line = 0;

            foreach($tokens as $token) {
                if(is_array($token)) {
                    $line = $token[2];
                }

                if(!is_array($token) || $token[0] != T_WHITESPACE)
                    switch($state) {
                        case 0:
                            if(is_array($token) && ($token[0] == T_OBJECT_OPERATOR || $token[0] == T_DOUBLE_COLON)) {
                                $state++;
                            }

                            break;
                        case 1:
                            if(is_array($token) && $token[0] == T_STRING && $token[1] == 'phrase') {
                                $state++;
                            }
                            else {
                                $state = 0;
                            }

                            break;
                        case 2:
                            if($token == '(') {
                                $state++;
                            }
                            else {
                                $state = 0;
                                $this->tokenWarningPhp('Unexpected Token', $token, $file, $line);
                            }

                            break;
                        case 3:
                            if(is_array($token) && $token[0] == T_CONSTANT_ENCAPSED_STRING) {
                                $id = eval('return '.$token[1].';');
                                $state++;

                                if(!isset($vars['instances']['id'][$id])) {
                                    $vars['instances']['id'][$id] = array();
                                }

                                $vars['instances']['id'][$id][] = array(
                                    'filename' => $file,
                                    'line' => $line
                                );

                                if(!isset($vars['instances']['file'][$file])) {
                                    $vars['instances']['file'][$file] = array();
                                }

                                $vars['instances']['file'][$file][] = array(
                                    'id' => $id,
                                    'line' => $line
                                );
                            }
                            else {
                                $state = 0;
                                $this->tokenWarningPhp('Unexpected Token', $token, $file, $line);
                            }

                            break;
                        case 4:
                            if($token != ')' && $token != ',') {
                                $this->tokenWarningPhp('Unexpected Token', $token, $file, $line);
                            }

                            $state = 0;

                            break;
                    }
            }
        }

        $vars['missing'] = $this->getMissing(array_keys($vars['instances']['id']));

        return $this->render('DevBundle:Language:find.phrases.php.html.twig', $vars);
    }

    public function checkLanguageFilesAction()
    {
        $files = $this->getLanguageFileList();
        $by_id = array();
        $by_content = array();
        $vars = array(
            'dupes' => array
            (
                'id' => array(),
                'content' => array()
            )
        );

        foreach($files as $file) {
            $rawphp = file_get_contents($file);
            $tokens = token_get_all($rawphp);
            $state = 0;

            foreach($tokens as $token) {
                // This is a very minimal parser and may break if the pec changes for lang file definitions.
                if(!is_array($token) && $token[0] != T_WHITESPACE)
                    switch($state) {
                        case 0:
                            if(is_array($token) && $token[0] == T_CONSTANT_ENCAPSED_STRING) {
                                $state++;
                                $id = eval('return '.$token[1].';');
                            }

                            break;
                        case 1:
                            if(is_array($token) && $token[0] == T_DOUBLE_ARROW) {
                                $state++;
                            }
                            else {
                                die('Unexpected token!');
                            }

                            break;
                        case 2:
                            if(is_array($token) && $token[0] == T_CONSTANT_ENCAPSED_STRING) {
                                $content = eval('return '.$token[1].';');
                                $state = 0;

                                if(!isset($by_id[$id])) {
                                    $by_id[$id] = array();
                                }

                                $by_id[$id][] = array(
                                    'filename' => $file,
                                    'content' => $content,
                                    'line' => $token[2]
                                );

                                if(!isset($by_content[$content])) {
                                    $by_content[$content] = array();
                                }

                                $by_content[$content][] = array(
                                    'filename' => $file,
                                    'content' => $id,
                                    'line' => $token[2]
                                );
                            }
                            else {
                                die('Unexpected token in '.$file.'!');
                            }

                            break;
                    }
            }
        }

        foreach($by_id as $k=>$v) {
            if(count($v) > 1) {
                $vars['dupes']['id'][] = $k;
            }
        }

        foreach($by_content as $k=>$v) {
            if(count($v) > 1) {
                $vars['dupes']['content'][] = $k;
            }
        }

        $vars['by_id'] = $by_id;
        $vars['by_content'] = $by_content;

        return $this->render('DevBundle:Language:check.langfiles.html.twig', $vars);
    }

    public function getLanguageData()
    {
        $files = $this->getLanguageFileList();
        $data = array();

        foreach($files as $file) {
            $data = array_merge($data, require($file));
        }

        return $data;
    }

    public function getLanguageFileList()
    {
        $rootdir = DP_ROOT.'/languages/DeskPRO';
        $dh1 = opendir($rootdir);
        $files = array();
        
        while(false !== ($dirname = readdir($dh1))) {
            if($dirname == '.' || $dirname == '..')
                continue;

            $path = $rootdir.'/'.$dirname;

            if(is_dir($path)) {
                $dh2 = opendir($path);

                while(false !== ($filename = readdir($dh2))) {
                    $filepath = $path.'/'.$filename;

                    if(is_file($filepath) && substr($filepath, -3 == 'php')) {
                        $files[] = $filepath;
                    }
                }

                closedir($dh2);
            }
        }
        
        closedir($dh1);
        return $files;
    }

    public function getPhpFileList()
    {
        // The use of references is generally discouraged in PHP, but this is a dev tool, so we can let it slide :).
        // Same goes for inline function.
        function scan_dir($dir, &$files)
        {
            if ($handle = opendir($dir)) {
                while (false !== ($filename = readdir($handle))) {
                    if (is_dir($dir.'/'.$filename)) {
                        if ($filename == "." || $filename == "..")
                                continue;

                        scan_dir($dir.'/'.$filename, $files);
                    }
                    else {
                        if(preg_match('/\.php$/i' , $filename)) {
                            $files[] = $dir.'/'.$filename;
                        }
                    }
                }

                closedir($handle);
            }
        }

        $files = array();
        // Create a large list containing all php files.
        scan_dir(DP_ROOT . '/src', $files);

        return $files;
    }

    public function getTwigFileList($bundle = null)
    {
        $bundles = array('AgentBundle', 'AdminBundle', 'InstallBundle', 'UserBundle', 'ReportBundle', 'BillingBundle', 'DeskPRO');

        if($bundle)
            $bundles = array($bundle);

        function scan_dir($dir, &$files)
        {
            if ($handle = opendir($dir)) {
                while (false !== ($filename = readdir($handle))) {
                    if (is_dir($dir.'/'.$filename)) {
                        if ($filename == "." || $filename == "..")
                                continue;

                        scan_dir($dir.'/'.$filename, $files);
                    }
                    else {
                        if(preg_match('/\.html.twig$/i' , $filename)) {
                            $files[] = $dir.'/'.$filename;
                        }
                    }
                }

                closedir($handle);
            }
        }

        $files = array();

        foreach($bundles as $bundle)
            scan_dir(DP_ROOT . '/src/Application/'.$bundle, $files);

        return $files;
    }

    public function dumpTokensPHP($tokens)
    {
        foreach($tokens as $i=>$token) {
            if(is_array($token)) {
                $tokens[$i][] = token_name($token[0]);
            }
        }

        print_r($tokens);
    }

    public function dumpTokensTwig($tokens)
    {
        $basic_tokens = array();

        while(!$tokens->isEOF()) {
            $token = $tokens->next();
            $basic_tokens[] = array(
                'type' => \Twig_Token::TypeToString($token->getType(), true),
                'value' => $token->getValue(),
            );
        }

        print_r($basic_tokens);
    }

    public function tokenWarningPhp($message, $token, $file, $line)
    {
        if(is_array($token))
            $token = token_name($token[0]) .':'. $token[1];

        echo "Warning: {$message} ($token) in {$file}:{$line}<br />";
    }

    public function tokenWarningTwig($message, $token, $file, $line)
    {
        $token = \Twig_Token::TypeToString($token->getType(), true) .':'.$token->getValue();

        echo "Warning: {$message} ($token) in {$file}:{$line}<br />";
    }

    public function getMissing($ids)
    {
        $language_data = $this->getLanguageData();
        $missing = array();

        foreach($ids as $id) {
            if(!array_key_exists($id, $language_data)) {
                $missing[] = $id;
            }
        }

        return $missing;
    }

    public function stripTwig($tokens)
    {
        $html = '';

        while(!$tokens->isEOF()) {
            $token = $tokens->next();

            if($token->getType() == \Twig_Token::TEXT_TYPE) {
                $html .= $token->getValue();
            }
        }

        return $html;
    }

    public function twigTokensToString($tokens)
    {
        $html = '';

        while(!$tokens->isEOF()) {
            $token = $tokens->next();
            $html .= $token->getValue();
        }

        return $html;
    }

    public function getTwigPreservingLexer($options)
    {
        return new \Twig_PreservingLexer($this->container->get('twig'), $options);
    }

    public function getTwigLexer($options)
    {
        return new \Twig_Lexer($this->container->get('twig'), $options);
    }
}
