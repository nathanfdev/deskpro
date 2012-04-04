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
use Application\DeskPRO\Translate;
use Application\DeskPRO\App;
use Symfony\Component\HttpFoundation\Response;
use Application\DevBundle\Twig\PreservingLexer;
use Orb\Util\Strings;
use Orb\Util\Arrays;

class LanguageController extends Controller
{
    private $files_temp;
    private $bundles = array('AgentBundle', 'AdminBundle', 'InstallBundle', 'UserBundle', 'ReportBundle', 'BillingBundle', 'DeskPRO');

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

    public function testTokenizerAction()
    {
        $templates = $this->getPHPFileList();
        $mismatches = array();

        foreach($templates as $file) {

            $raw_php = file_get_contents($file);
            $tokens = token_get_all($raw_php);
            $new_raw_php = $this->phpTokensToString($tokens);

            if($raw_php != $new_raw_php) {
                file_put_contents('./tmp', $new_raw_php);
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

    public function testLexerAction()
    {
        $lexer = $this->getTwigPreservingLexer(array());
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

    public function replacePhrasesInFiles($files, $from, $to, $prefix = false)
    {
        foreach($files as $file) {
            $lines = file($file['filename']);
            $data = '';

            foreach($lines as $i=>$line) {
                if($prefix) {
                    if(preg_match('/(phrase\(\s*\')'.preg_quote($from, '/').'([^\']+\'\s*[,)])/', $line)) {
                        $new_line = preg_replace('/(phrase\(\s*\')'.preg_quote($from, '/').'([^\']+\'\s*[,)])/', '\1'.$to.'\2', $line);
                        echo "Replacing line ".htmlspecialchars($line)." <br />with ".htmlspecialchars($new_line)."<br />";
                        $data .= $new_line;
                    }
                    else {
                        $data .= $line;
                    }
                }
                else {
                    if(preg_match('/(phrase\(\s*\')'.preg_quote($from, '/').'(\'\s*[,)])/', $line)) {
                        $new_line = preg_replace('/(phrase\(\s*\')'.preg_quote($from, '/').'(\'\s*[,)])/', '\1'.$to.'\2', $line);
                        echo "Replacing line ".htmlspecialchars($line)." <br />with ".htmlspecialchars($new_line)."<br />";
                        $data .= $new_line;
                    }
                    else {
                        $data .= $line;
                    }
                }
            }

            file_put_contents($file['filename'], $data);
        }
    }

    public function flatFileListToNonFlat($flat_files)
    {
        $files = array();

        foreach($flat_files as $file) {
            $files[] = array('filename' => $file);
        }

        return $files;
    }

    public function exportAllToPOAction()
    {
        $files = $this->getLanguageFileList();
        $strings = array();

        foreach($files as $file) {
            $strings = array_merge(require($file), $strings);
        }

        $vars = array(
            'meta' => array(

            ),
            'strings' => $strings
        );
        $response = new Response();
        $response->headers->set('Content-Type','text/html');

        return $response;
    }

    public function replacePhraseIdsAction()
    {
        $bundles = $this->bundles;
        $vars = array('bundles' => $bundles);
        set_time_limit(0);

        if(isset($_POST['from'])) {
            $from = $_POST['from'];
            $to = $_POST['to'];
            $in = $_POST['bundles'];
            $prefix = isset($_POST['prefix']) && $_POST['prefix'];
            $files = array();

            foreach($in as $bundle) {
                $files = array_merge($this->flatFileListToNonFlat($this->getPhpFileList($bundle)), $files);
                $files = array_merge($this->flatFileListToNonFlat($this->getTwigFileList($bundle)), $files);
            }

            $this->replacePhrasesInFiles($files, $from, $to, $prefix);
        }

        return $this->render('DevBundle:Language:replace.phrases.html.twig', $vars);
    }

    public function findForeignIdsAction()
    {
        set_time_limit(0);
        $vars = array();

        $bundle_map = array(
            'AgentBundle' => 'agent',
            'ReportBundle' => 'agent',
            'AdminBundle' => 'admin',
            'BillingBundle' => 'admin',
            'UserBundle' => 'user'
        );
        $foreign = array();

        foreach(array_keys($bundle_map) as $bundle) {
            $foreign[$bundle] = array();
        }

        $globals = array('agent' => array(), 'admin' => array(), 'user' => array());
        $rootdir = DP_ROOT.'/languages/DeskPRO';
        $real_global = require($rootdir.'/global/global.php');

        foreach($bundle_map as $bundle=>$lang) {
            ob_start();
            list($by_id_php, ) = $this->getPhrasesFromPHPFiles($bundle);
            list($by_id_twig, ) = $this->getPhrasesFromTwigFiles($bundle);
            ob_end_clean();

            foreach(array($by_id_php, $by_id_twig) as $by_id) {
                foreach($by_id as $id=>$files) {
                    list($folder, $remaining) = explode('.', $id, 2);

                    if($lang != $folder) {
                        if(!isset($foreign[$bundle][$id])) {
                            $foreign[$bundle][$id] = array();
                        }

                        $foreign[$bundle][$id] = array_merge($files, $foreign[$bundle][$id]);
                    }
                }
            }
        }

        if(isset($_POST['globals'])) {
            foreach($foreign as $bundle=>$f) {
                foreach($f as $id=>$files) {
                    list($firstpart, ) = explode('.', $id, 2);

                    if($firstpart == 'global') {
                        if(!in_array($id, $globals[$bundle_map[$bundle]])) {
                            $globals[$bundle_map[$bundle]][$id] = $files;
                        }
                    }
                }
            }

            foreach($globals as $prefix=>$ids) {
                $export = array();
                $replace_files = array();

                foreach($ids as $id=>$files) {
                    $replace_files = array_merge($files, $replace_files);
                    $export[$prefix.'.'.$id] = $real_global[$id];
                }

                $globals[$prefix] = '<?php return '.var_export($export, true).';';

                $this->replacePhrasesInFiles($replace_files, 'global.', $prefix.'.global.', true);
                file_put_contents($rootdir.'/'.$prefix.'/global.php', $globals[$prefix]);
            }
        }

        $vars['foreign'] = $foreign;
        return $this->render('DevBundle:Language:find.foreign.html.twig', $vars);
    }


    public function findRawStringsAction($bundle)
    {
        set_time_limit(0);
        $langfile = array();

        if(isset($_POST['replace'])) {
            foreach($_POST['replace'] as $replace) {
                list($file, $string) = explode('?', $replace, 2);
                $id = $this->stringToId($string);
                $prefix = strtolower(str_replace('Bundle', '', $bundle).'.'.basename(dirname($file)));
                $conf_dir = DP_ROOT.'/languages/DeskPRO/'.strtolower(str_replace('Bundle', '', $bundle));

                if(!is_dir($conf_dir)) {
                    mkdir($conf_dir , 0755, true);
                }

                $conf_file = $conf_dir.'/'.strtolower(basename(dirname($file))).'.php';

                if(!file_exists($conf_file)) {
                    file_put_contents($conf_file, '<?php return ;');
                }

                if(!isset($langfile[$conf_file])) {
                    $langfile[$conf_file] = array();
                }

                $id = $prefix.'.'.$id;
                $langfile[$conf_file][$id] = $string;
                $data = file_get_contents($file);
                $lines = file($file);
                $new_data = '';

                foreach($lines as $line) {
                    $line = $this->replaceInLine($line, $string, $id);
                    $new_data .= $line;
                }

                //$new_data = str_replace($string, '{{ phrase(\''.$id.'\') }}', $data);

                if($data != $new_data) {
                    $langfile[$conf_file][$id] = $string;

                    if(!isset($_POST['dry_run'])) {
                        file_put_contents($file, $new_data);
                    }
                }
            }
        }

        $twig_options = array();
        $lexer = $this->getTwigLexer($twig_options);
        $templates = $this->getTwigFileList($bundle);
        $untranslated = array();
        $filenames = array();

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
                    $string_multi = explode("\n", $string);
                    $string_multi[] = str_replace("\n", ' ', $string);

                    foreach($string_multi as $string) {
                        $string = preg_replace('/^\s*/', '', $string);
                        $string = preg_replace('/\s*$/', '', $string);

                        if($string == '' || strlen($string) == 1) {
                            continue;
                        }

                        if(!preg_match('/[a-zA-Z]/', $string)) {
                            continue;
                        }

                        if(!in_array($string, $strings)) {
                            $strings[] = $string;
                        }
                    }
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
                $tidy = str_replace('&nbsp;', '&#xA0;', $tidy);
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
            $strings_ids = array();
            $lines = explode("\n", $raw_twig);

            preg_match_all('/(?:placeholder|alt|title)="([^$@"{\'][^"]+)"/', $raw_twig, $matches);

            if(!empty($matches[1])) {
                foreach($matches[1] as $match) {
                    if(!in_array($match, $strings)) {
                        $strings[] = $match;
                    }
                }
            }

            $stripped = strip_tags($html);

            foreach($strings as $string) {
                $context = array();
                $id = $this->stringToId($string);

                foreach($lines as $line) {
                    $pos = strpos($line, $string);

                    if(preg_match('/.{0,64}'.preg_quote($string, '/').'.{0,64}/', $line, $match)) {
                        $out = $this->replaceInLine($line, $string, $id);

                        if($out != $line) {
                            $out = $this->replaceInLine($line, $string, $id, true);
                            $line = str_replace($string, "<span style=\"color:red\">$string</span>", htmlspecialchars($line));
                            $context[] = array('in' => $line, 'out' => $out);
                        }
                    }
                }

                if(count($context)) {
                    $strings_ids[] = array('text' => $string, 'id' => $id, 'context' => $context);
                }
            }

            if(count($strings)) {
                $untranslated[$file] = $strings_ids;
                $filenames[$file] = basename(dirname($file)).'/'.basename($file);
            }

            unset($strings);
        }

        $lang_conf = array();

        foreach($langfile as $file => $strings) {
            $lang_conf[$file] = var_export($strings, true);
        }

        $vars = array('untranslated' => $untranslated, 'langfiles' => $lang_conf, 'filenames' => $filenames);

        return $this->render('DevBundle:Language:find.raw.html.twig', $vars);
    }

    public function getPhrasesFromTwigFiles($bundle = null)
    {
        $twig = $this->container->get('twig');
        $templates = $this->getTwigFileList($bundle);
        $by_id = array();
        $by_file = array();

        foreach($templates as $file) {
            $raw_twig = file_get_contents($file);
            $tokens = $twig->tokenize($raw_twig);
            $state = 0;

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
                        else
                        {
                            if(!isset($by_id[$id])) {
                                $by_id[$id] = array();
                            }

                            $by_id[$id][] = array(
                                'filename' => $file,
                                'line' => $line
                            );

                            if(!isset($by_file[$file])) {
                                $by_file[$file] = array();
                            }

                            $by_file[$file][] = array(
                                'id' => $id,
                                'line' => $line
                            );
                        }

                        $state = 0;

                        break;
                }
            }
        }

        return array($by_id, $by_file);
    }

    public function findPhrasesInTwigFilesAction()
    {
        $vars = array(
            'instances' => array
            (
                'id' => array(),
                'file' => array()
            ),
            'missing' => array()
        );

        list($vars['instances']['id'], $vars['instances']['file']) = $this->getPhrasesFromTwigFiles();
        $vars['missing'] = $this->getMissing(array_keys($vars['instances']['id']));

        return $this->render('DevBundle:Language:find.phrases.twig.html.twig', $vars);
    }

    public function getPhrasesFromPHPFiles()
    {
        $files = $this->getPhpFileList();
        $by_id = array();
        $by_file = array();

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
                            else
                            {
                                if(!isset($by_id[$id])) {
                                    $by_id[$id] = array();
                                }

                                $by_id[$id][] = array(
                                    'filename' => $file,
                                    'line' => $line
                                );

                                if(!isset($by_file[$file])) {
                                    $by_file[$file] = array();
                                }

                                $by_file[$file][] = array(
                                    'id' => $id,
                                    'line' => $line
                                );
                            }

                            $state = 0;

                            break;
                    }
            }
        }

        return array($by_id, $by_file);
    }

    public function findPhrasesInPHPFilesAction()
    {
        $vars = array(
            'instances' => array
            (
                'id' => array(),
                'file' => array()
            ),
            'missing' => array()
        );

        list($vars['instances']['id'], $vars['instances']['file']) = $this->getPhrasesFromPHPFiles();


        $vars['missing'] = $this->getMissing(array_keys($vars['instances']['id']));

        return $this->render('DevBundle:Language:find.phrases.php.html.twig', $vars);
    }

    public function parseLangFiles()
    {
        $files = $this->getLanguageFileList();
        $by_id = array();
        $by_content = array();

        foreach($files as $file) {
            $rawphp = file_get_contents($file);
            $tokens = token_get_all($rawphp);
            $state = 0;

            foreach($tokens as $token) {
                // This is a very minimal parser and may break if the pec changes for lang file definitions.
                if(!is_array($token) || $token[0] != T_WHITESPACE)
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
                                    'id' => $id,
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

        return array($by_id, $by_content);
    }

    public function globaliseString($content, $id, $twig_phrases, $php_phrases)
    {
        list($by_id, $by_content) = $this->parseLangFiles();
        $rootdir = DP_ROOT.'/languages/DeskPRO';
        $global = require($rootdir.'/global/global.php');

        if(!isset($global[$id])) {
            $global[$id] = $content;
            $data = '<?php return '.var_export($global, true).';';
            echo "Would put ".htmlspecialchars($data)."<br>";
            file_put_contents($rootdir.'/global/global.php', $data);
        }

        $files = $by_content[$content];

        foreach($files as $file) {
            $lines = file($file['filename']);
            $data = '';

            foreach($lines as $i => $line) {
                if($i+1 == $file['line']) {
                    echo "Dropping line $line<br />";
                    $data .= "\n";
                }
                else {
                    $data .= $line;
                }
            }

            file_put_contents($file['filename'], $data);
        }

        list($by_id, ) = $twig_phrases;

        foreach($files as $file) {
            if(!isset($by_id[$file['id']]))
                continue;

            $this->replacePhrasesInFiles($by_id[$file['id']], $file['id'], $id);
        }

        list($by_id, ) = $php_phrases;

        foreach($files as $file) {
            if(!isset($by_id[$file['id']]))
                continue;

            $this->replacePhrasesInFiles($by_id[$file['id']], $file['id'], $id);
        }
    }

    public function checkLanguageFilesAction()
    {
        set_time_limit(0);
        if(isset($_POST['batch'])) {
            $twig_phrases = $this->getPhrasesFromTwigFiles();
            $php_phrases = $this->getPhrasesFromPHPFiles();

            foreach($_POST['batch'] as $content) {
                $this->globaliseString($content,'global.'.$this->stringToId($content), $twig_phrases, $php_phrases);
            }
        } else if(isset($_POST['content'])) {
            $this->globaliseString($_POST['content'], $_POST['id'], $this->getPhrasesFromTwigFiles(), $this->getPhrasesFromPHPFiles());
        }

        $vars = array(
            'dupes' => array
            (
                'id' => array(),
                'content' => array()
            )
        );

        list($by_id, $by_content) = $this->parseLangFiles();

        foreach($by_id as $k=>$v) {
            if(count($v) > 1) {
                $vars['dupes']['id'][] = $k;
            }
        }

        $id_track = array();
        $rootdir = DP_ROOT.'/languages/DeskPRO';
        $global = require($rootdir.'/global/global.php');

        foreach($by_content as $k=>$v) {
            if(count($v) > 1) {
                $id = 'global.'.$this->stringToId($k);
                $id_exists = isset($global[$id]) || isset($id_track[$id]);

                $vars['dupes']['content'][] = array('data' => $k, 'id' => $id, 'exists' => $id_exists);
                $id_track[$id] = 1;
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

    public function getPhpFileList($bundle = null)
    {
        if($bundle)
            $bundles = array($bundle);
        else
            $bundles = $this->bundles;

        $this->files_temp = array();

        foreach($bundles as $bundle)
            $this->scanDir(DP_ROOT . '/src/Application/'.$bundle, '.php');

        return $this->files_temp;
    }

    function scanDir($dir, $suffix)
    {
        if ($handle = opendir($dir)) {
            while (false !== ($filename = readdir($handle))) {
                if (is_dir($dir.'/'.$filename)) {
                    if ($filename == "." || $filename == "..")
                        continue;

                    $this->scanDir($dir.'/'.$filename, $suffix);
                }
                else {
                    if(preg_match('/'.preg_quote($suffix, '/').'$/i' , $filename)) {
                        $this->files_temp[] = $dir.'/'.$filename;
                    }
                }
            }

            closedir($handle);
        }
    }

    public function getTwigFileList($bundle = null)
    {
        if($bundle)
            $bundles = array($bundle);
        else
            $bundles = $this->bundles;

        $this->files_temp = array();

        foreach($bundles as $bundle)
            $this->scanDir(DP_ROOT . '/src/Application/'.$bundle, '.html.twig');

        return $this->files_temp;
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

    public function phpTokensToString($tokens)
    {
        $html = '';

        foreach($tokens as $token) {
            if(is_array($token)) {
                $html .= $token[1];
            }
            else {
                $html .= $token;
            }
        }

        return $html;
    }

    public function getTwigPreservingLexer($options)
    {
        return new PreservingLexer($this->container->get('twig'), $options);
    }

    public function getTwigLexer($options)
    {
        return new \Twig_Lexer($this->container->get('twig'), $options);
    }

    public function stringToId($string)
    {
        $string = strtolower($string);
        $string = preg_replace('/[^a-zA-Z0-9_ ]/', '', $string);
        $string = preg_replace('/ +/', ' ', $string);
        $parts = explode(' ', $string);
        $parts = array_slice($parts, 0, 8);
        $string = implode('_', $parts);
        return $string;
    }

    public function replaceInFile($text, $string, $id, $color = false)
    {
        if(strpos($text, $string) !== false) {
            if($color) {
                return str_replace($string, "<span style=\"color:red\">{{ phrase('{$id}') }}</span>", htmlspecialchars($text, ENT_NOQUOTES));
            }
            else {
                return str_replace($string, "{{ phrase('{$id}') }}", $text);
            }
        }

        return $text;
    }

    public function replaceInLine($line, $string, $id, $color = false)
    {
        // I think I need too check my regex book. This can't be good!
        $left = '/(.*(?:^|[\'>}])[^a-zA-Z]*)';
        $right = '([^a-zA-Z]*(?:[{<\']|$))/';
        $def_left = '/default\(\'';
        $def_right = '\'\)/';
        $ol_left = '/(op_lang[^}]+:\s*)\'';
        $ol_right = '\'/';
        $attr_left = '/((?:placeholder|(?:type="submit"[^<>]*value)|alt|title)="\s*)';
        $attr_right = '(\s*")/';

        // Reduce mistakes.
        if(preg_match($def_left.preg_quote($string, '/').$def_right, $line)) {
            if($color) {
                $line = htmlspecialchars($line, ENT_NOQUOTES);
                $line = str_replace("default('{$string}'", "default(<span style=\"color:green;\">phrase('{$id}')</span>)", $line);
            }
            else {
                $line = str_replace("default('{$string}'", "default(phrase('{$id}')", $line);
            }
        } elseif(preg_match($ol_left.preg_quote($string, '/').$ol_right, $line)) {
            if($color) {
                $line = htmlspecialchars($line, ENT_NOQUOTES);
                $line = preg_replace($ol_left.preg_quote($string, '/').$ol_right, "\\1<span style=\"color:green;\">phrase('{$id}')</span>", $line);
            }
            else {
                $line = preg_replace($ol_left.preg_quote($string, '/').$ol_right, '\1phrase(\''.$id.'\')', $line);
            }
        } elseif(preg_match($left.preg_quote($string, '/').$right, $line, $matches)) {
            if(!preg_match('/\{(\%|\{|\#)[^}]*$/', $matches[1])) {
                if($color) {
                    $line = preg_replace($left.preg_quote($string, '/').$right, '\1__CSPAN__{{ phrase(\''.$id.'\') }}__ENDCSPAN__\2', $line);
                    $line = htmlspecialchars($line);
                    $line = str_replace('__ENDCSPAN__', '</span>', $line);
                    $line = str_replace('__CSPAN__', '<span style="color:red;">', $line);
                }
                else {
                    $line = preg_replace($left.preg_quote($string, '/').$right, '\1{{ phrase(\''.$id.'\') }}\2', $line);
                }
            }
        }

        if(preg_match($attr_left.preg_quote($string, '/').$attr_right, $line)) {
            if($color) {
                $line = preg_replace($attr_left.preg_quote($string, '/').$attr_right, '\1__CSPAN__{{ phrase(\''.$id.'\') }}__ENDCSPAN__\2', $line);
                $line = htmlspecialchars($line);
                $line = str_replace('__ENDCSPAN__', '</span>', $line);
                $line = str_replace('__CSPAN__', '<span style="color:blue;">', $line);
            }
            else {
                $line = preg_replace($attr_left.preg_quote($string, '/').$attr_right, '\1{{ phrase(\''.$id.'\') }}\2', $line);
            }
        }


        return $line;
    }
}