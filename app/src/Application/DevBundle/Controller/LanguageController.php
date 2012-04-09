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
use Application\DevBundle\Language\Language;

use Orb\Util\Strings;
use Orb\Util\Arrays;

class LanguageController extends Controller
{
    public function indexAction()
    {
		return $this->render('DevBundle:Language:index.html.twig', array('bundles' => Language::$BUNDLES, 'bundle_map' => Language::$BUNDLES_MAP, 'packages' => Language::$PACKAGES));
    }

    public function listLanguageFilesAction()
    {
		return $this->render('DevBundle:Language:simplelist.html.twig', array('list'=>Language::GetFileFinder()->getLanguageFileList()));
    }

    public function listPhpFilesAction()
    {
		return $this->render('DevBundle:Language:simplelist.html.twig', array('list'=>Language::GetFileFinder()->getPhpFileList()));
    }

    public function listTwigFilesAction()
    {
		return $this->render('DevBundle:Language:simplelist.html.twig', array('list'=>Language::GetFileFinder()->getTwigFileList()));
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
        $templates = Language::GetFileFinder()->getTwigFileList();
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

    public function exportToPOAction($package)
    {
        set_time_limit(0);
        $files = Language::GetFileFinder()->getLanguageFileList($package);
        $strings = array();

        foreach($files as $file) {
            $strings = array_merge(require($file), $strings);
        }

        $fs = fopen(DP_ROOT.'/tmp.csv', 'w');
        fputcsv($fs, array('location', 'source', 'target'));

        /*foreach($strings as $source => $target) {
            fputcsv($fs, array('', $source, str_replace("\n",'\n', $target)));
        }*/

        fclose($fs);
        //echo shell_exec('csv2po '.DP_ROOT.DIRECTORY_SEPARATOR.'tmp.csv '.DP_ROOT.DIRECTORY_SEPARATOR.'tmp.po');

        $fs = fopen(DP_ROOT.'/tmp.po', 'a');

        foreach($strings as $source => $target) {
            fwrite($fs, "\nmsgid \"{$source}\"\n");
            fwrite($fs, "msgstr ");
            $parts = explode("\n", $target);

            foreach($parts as $i=>$part) {
                fwrite($fs, '"'.$part);

                if($i != count($parts) -1) {
                    fwrite($fs, '\n');
                }

                fwrite($fs, "\"\n");
            }
        }

        fclose($fs);

        $response = new Response();
        $response->headers->set('Content-Type','text/po');
        $response->headers->set('Content-Disposition', ' attachment; filename=languages_'.$package.'.po');
        $response->setContent(file_get_contents(DP_ROOT.DIRECTORY_SEPARATOR.'tmp.po'));
        unlink(DP_ROOT.DIRECTORY_SEPARATOR.'tmp.csv');
        unlink(DP_ROOT.DIRECTORY_SEPARATOR.'tmp.po');

        return $response;
    }

    public function exportAllToPOAction()
    {
        return $this->exportToPOAction('');
    }

    public function replacePhraseIdsAction()
    {
        $bundles = Language::$BUNDLES;
        $vars = array('bundles' => $bundles);
        set_time_limit(0);

        if(isset($_POST['from'])) {
            $from = $_POST['from'];
            $to = $_POST['to'];
            $in = $_POST['bundles'];
            $prefix = isset($_POST['prefix']) && $_POST['prefix'];
            $files = array();

            foreach($in as $bundle) {
                $files = array_merge(Language::GetFileFinder()->flatFileListToNonFlat(Language::GetFileFinder()->getPhpFileList($bundle)), $files);
                $files = array_merge(Language::GetFileFinder()->flatFileListToNonFlat(Language::GetFileFinder()->getTwigFileList($bundle)), $files);
            }

            $this->replacePhrasesInFiles($files, $from, $to, $prefix);
        }

        return $this->render('DevBundle:Language:replace.phrases.html.twig', $vars);
    }

    public function findForeignIdsAction($bundle)
    {
        set_time_limit(0);
        $vars = array();
        $foreign = array();

        $rootdir = DP_ROOT.'/languages/DeskPRO';

        $lang = Language::$BUNDLES_MAP[$bundle];

        ob_start();
        list($by_id_php, ) = Language::getPhraseFinder($this->container)->getPhrasesFromPHPFiles($bundle);
        list($by_id_twig, ) = Language::getPhraseFinder($this->container)->getPhrasesFromTwigFiles($bundle);
        ob_end_clean();

        foreach(array($by_id_php, $by_id_twig) as $by_id) {
            foreach($by_id as $id=>$files) {
                list($folder, $remaining) = explode('.', $id, 2);

                if($lang != $folder) {
                    if(!isset($foreign[$id])) {
                        $foreign[$id] = array();
                    }

                    $foreign[$id] = array_merge($files, $foreign[$id]);
                }
            }
        }

        if(isset($_POST['globals'])) {
            $real_global = require($rootdir.'/global/global.php');
            $globals = array();

            foreach($foreign as $id=>$files) {
                list($firstpart, ) = explode('.', $id, 2);

                if($firstpart == 'global') {
                    $globals[$id] = $files;
                }
            }

            $export = array();

            if(file_exists($rootdir.'/'.$lang.'/global.php')) {
                $export = require($rootdir.'/'.$lang.'/global.php');
            }

            $replace_files = array();

            foreach($globals as $id=>$files) {
                foreach($files as $file) {
                    $replace_files[$file['filename']] = $file;
                }

                $export[$lang.'.'.$id] = $real_global[$id];
            }

            $globals = '<?php return '.var_export($export, true).';';
            $this->replacePhrasesInFiles($replace_files, 'global.', $lang.'.global.', true);
            file_put_contents($rootdir.'/'.$lang.'/global.php', $globals);
        }

        if(isset($_POST['foreign'])) {
            $foreigners = array();

            foreach($foreign as $id=>$files) {
                list($firstpart, ) = explode('.', $id, 2);

                if(in_array($firstpart, Language::$PACKAGES)
                && !($firstpart == 'user' && $bundle == 'DeskPRO')) {
                    $foreigners[$id] = $files;
                }
            }

            foreach($foreigners as $id=>$files) {
                foreach($files as $file) {
                    $package = $lang;

                    if($bundle == 'DeskPRO') {
                        if(preg_match('#/[^/]*user[^/]*/[^/]*.html.twig$#', $file['filename'])) {
                            $package = 'user';
                        }
                    }

                    $last_parts = $parts = explode('.', $id, 3);
                    $firstpart = array_shift($last_parts);
                    array_unshift($last_parts, $package);

                    $dst_file = $rootdir.'/'.$package.'/';

                    if(count($parts) == 3) {
                        $dst_file .= $parts[1].'.php';
                    }
                    else {
                        $dst_file .= $package.'.php';
                    }

                    $src_file = $rootdir.'/'.$firstpart.'/';

                    if(count($parts) == 3) {
                        $src_file .= $parts[1].'.php';
                    }
                    else {
                        $src_file .= $firstpart.'.php';
                    }

                    $source = require($src_file);

                    if(isset($source[$id])) {
                        $content = $source[$id];
                    }
                    else {
                        $content = '';
                    }

                    if(file_exists($dst_file)) {
                        $target = require($dst_file);
                    }
                    else {
                        $target = array();
                    }

                    $new_id = implode('.',$last_parts);

                    $target[$new_id] = $content;
                    file_put_contents($dst_file, '<?php return '.var_export($target, true).';');

                    $this->replacePhrasesInFiles(array($file), $id, $new_id, false);
                }
            }
        }

        if(isset($_POST['core'])) {
            $cores = array();

            foreach($foreign as $id=>$files) {
                list($firstpart, ) = explode('.', $id, 2);

                if(preg_match('/^core/', $firstpart)) {
                    $foreigners[$id] = $files;
                }
            }

            foreach($foreigners as $id=>$files) {
                foreach($files as $file) {
                    $package = $lang;
                    $parts = explode('.', $id, 3);
                    $core_parts = explode('_', $parts[0]);
                    array_shift($parts);

                    foreach(array_reverse($core_parts) as $core_part) {
                        array_unshift($parts, $core_part);
                    }

                    $last_parts = $parts;
                    array_shift($last_parts);
                    array_unshift($last_parts, $package);

                    $dst_file = $rootdir.'/'.$package.'/';

                    if(count($parts) == 3) {
                        $dst_file .= $parts[1].'.php';
                    }
                    else {
                        $dst_file .= $package.'.php';
                    }

                    if(file_exists($dst_file)) {
                        $target = require($dst_file);
                    }
                    else {
                        $target = array();
                    }

                    if(isset($target[$id])) {
                        $content = $target[$id];
                    }
                    else {
                        $content = '';
                    }

                    $new_id = implode('.',$last_parts);

                    $target[$new_id] = $content;
                    file_put_contents($dst_file, '<?php return '.var_export($target, true).';');
                    $this->replacePhrasesInFiles(array($file), $id, $new_id, false);
                }
            }
        }

        $vars['foreign'] = $foreign;
        return $this->render('DevBundle:Language:find.foreign.html.twig', $vars);
    }

    public function manualReplaceRawString()
    {
        return $this->render('DevBundle:Language:find.raw.html.twig', $vars);
    }

    public function fixMissing($missing) {
        $rootdir = DP_ROOT.'/languages/DeskPRO';
        $missing = array_unique($missing);

        foreach($missing as $id) {
            $parts = explode('.', $id, 3);
            $package = array_shift($parts);

            $dst_file = $rootdir.'/'.$package.'/';

            if(count($parts) == 2) {
                $dst_file .= $parts[0].'.php';
            }
            else {
                $dst_file .= $package.'.php';
            }

            if(file_exists($dst_file)) {
                $target = require($dst_file);
            }
            else {
                $target = array();
            }

            if(!isset($target[$id])) {
                $target[$id] = '['.$id.']';
                file_put_contents($dst_file, '<?php return '.var_export($target, true).';');
            }
        }
    }

    public function findPhrasesInTwigFilesAction()
    {
        $vars = array(
            'instances' => array
            (
                'id' => array(),
                'file' => array(),
            ),
            'prefixes' => array(),
            'missing' => array(),
        );

        list($vars['instances']['id'], $vars['instances']['file'], $vars['prefixes']) = Language::getPhraseFinder($this->container)->getPhrasesFromTwigFiles();
        $missing = $this->getMissing(array_keys($vars['instances']['id']));

        if(isset($_POST['missing'])) {
            $this->fixMissing($missing);
        }

        $vars['missing'] = $missing;

        return $this->render('DevBundle:Language:find.phrases.html.twig', $vars);
    }

    public function findPhrasesInPHPFilesAction()
    {
        $vars = array(
            'instances' => array
            (
                'id' => array(),
                'file' => array(),
            ),
            'prefixes' => array(),
            'missing' => array(),
        );

        list($vars['instances']['id'], $vars['instances']['file'], $vars['prefixes']) = Language::getPhraseFinder($this->container)->getPhrasesFromPHPFiles();
        $missing = $this->getMissing(array_keys($vars['instances']['id']));

        if(isset($_POST['missing'])) {
            $this->fixMissing($missing);
        }

        $vars['missing'] = $missing;

        return $this->render('DevBundle:Language:find.phrases.html.twig', $vars);
    }

    public function parseLangFiles($package = '')
    {
        $files = Language::GetFileFinder()->getLanguageFileList($package);
        $by_id = array();
        $by_content = array();

        foreach($files as $file) {
            $rawphp = file_get_contents($file);
            $tokens = token_get_all($rawphp);
            $state = 0;

            foreach($tokens as $token) {
                // This is a very minimal parser and may break if the spec changes for lang file definitions.
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
            $twig_phrases = Language::getPhraseFinder($this->container)->getPhrasesFromTwigFiles();
            $php_phrases = Language::getPhraseFinder($this->container)->getPhrasesFromPHPFiles();

            foreach($_POST['batch'] as $content) {
                $this->globaliseString($content,'global.'.$this->stringToId($content), $twig_phrases, $php_phrases);
            }
        } else if(isset($_POST['content'])) {
            $this->globaliseString($_POST['content'], $_POST['id'], Language::getPhraseFinder($this->container)->getPhrasesFromTwigFiles(), Language::getPhraseFinder($this->container)->getPhrasesFromPHPFiles());
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
        $files = Language::GetFileFinder()->getLanguageFileList();
        $data = array();

        foreach($files as $file) {
            $data = array_merge($data, require($file));
        }

        return $data;
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