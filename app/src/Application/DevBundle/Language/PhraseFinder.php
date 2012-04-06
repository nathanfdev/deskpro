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

namespace Application\DevBundle\Language;

class PhraseFinder
{
    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function getPhrasesFromTwigFiles($bundle = null)
    {
        $twig = Language::GetTwigLexer($this->container);
        $templates = Language::GetFileFinder()->getTwigFileList($bundle);
        $by_id = array();
        $by_file = array();
        $prefixes = array();

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
                            $prefixes[] = array('id' => $id, 'filename' => $file, 'line' => $line);
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

        return array($by_id, $by_file, $prefixes);
    }

    public function getPhrasesFromPHPFiles($bundle = null)
    {
        $files = Language::GetFileFinder()->getPhpFileList($bundle);
        $by_id = array();
        $by_file = array();
        $prefixes = array();

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
                                $prefixes[] = array('id' => $id, 'filename' => $file, 'line' => $line);
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

        return array($by_id, $by_file, $prefixes);
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
}