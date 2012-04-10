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

        $vars['instances']['file'] = array();
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
        $rootdir = DP_ROOT.'/languages';
        
        $parts = explode('.', $id, 3);
        
        if(count($parts) == 2) {
            $package = $parts[0];
            $filename = $parts[0];
        }
        else {
            $package = $parts[0];
            $filename = $parts[1];
        }

        $files = $by_content[$content];

        foreach($files as $i=>$file) {
            list($file_package, ) = explode('.', $file['id'], 2);

            if($package == 'user' && $file_package != 'user') {
                unset($files[$i]);
                continue;
            }

            if($package == 'agent' && $file_package == 'user') {
                unset($files[$i]);
                continue;
            }

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

        $global = require($rootdir.'/'.$package.'/'.$filename.'.php');

        if(!isset($global[$id])) {
            $global[$id] = $content;
            $data = '<?php return '.var_export($global, true).';';
            file_put_contents($rootdir.'/'.$package.'/'.$filename.'.php', $data);
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
        $rootdir = DP_ROOT.'/languages';

        foreach($by_content as $k=>$v) {
            if(count($v) > 1) {
                $packages = array('user' => array(),'admin' => array(), 'agent' => array());

                foreach($v as $dupe) {
                    list($package, ) = explode('.', $dupe['id']);

                    $packages[$package][] = $dupe['id'];
                }

                $packages['admin_agent'] = array_merge($packages['agent'], $packages['admin']);

                if(count($packages['user']) > 1) {
                    $id = 'user.global.'.$this->stringToId($k);
                    $id_exists = isset($by_id[$id]) || isset($id_track[$id]);
                    $id_track[$id] = 1;
                    $vars['dupes']['content'][] = array('data' => $k, 'id' => $id, 'exists' => $id_exists, 'ids' => $packages['user']);
                }

                if(count($packages['admin_agent']) > 1) {
                    if(count($packages['admin_agent']) == count($packages['admin'])) {
                        $id = 'admin.general.'.$this->stringToId($k);
                    }
                    else {
                        $id = 'agent.global.'.$this->stringToId($k);
                    }

                    $id_exists = isset($by_id[$id]) || isset($id_track[$id]);
                    $id_track[$id] = 1;
                    $vars['dupes']['content'][] = array('data' => $k, 'id' => $id, 'exists' => $id_exists, 'ids' => $packages['admin_agent']);
                }
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

    public function replacePhrasesInFiles($files, $from, $to, $prefix = false)
    {
        foreach($files as $file) {
            $lines = file($file['filename']);
            $data = '';

            foreach($lines as $i=>$line) {
                if($prefix) {
                    if(preg_match('/(phrase\(\s*\')'.preg_quote($from, '/').'([^\']+\'\s*[,)])/', $line)) {
                        $new_line = preg_replace('/(phrase\(\s*\')'.preg_quote($from, '/').'([^\']+\'\s*[,)])/', '\1'.$to.'\2', $line);
                        echo "Replacing line ".htmlspecialchars($line)." <br />with ".htmlspecialchars($new_line)."<br /> in {$file['filename']}<hr />";
                        $data .= $new_line;
                    }
                    else {
                        $data .= $line;
                    }
                }
                else {
                    if(preg_match('/(phrase\(\s*\')'.preg_quote($from, '/').'(\'\s*[,)])/', $line)) {
                        $new_line = preg_replace('/(phrase\(\s*\')'.preg_quote($from, '/').'(\'\s*[,)])/', '\1'.$to.'\2', $line);
                        echo "Replacing line ".htmlspecialchars($line)." <br />with ".htmlspecialchars($new_line)."<br /> in {$file['filename']}<hr />";
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
}