<?php

namespace Application\DeskPRO\Languages;

use Application\DeskPRO\DependencyInjection\SystemServices\LanguageDataService;

class Detect
{
    /**
     * @var \Application\DeskPRO\DependencyInjection\SystemServices\LanguageDataService
     */
    protected $lang_data;

    /**
     * @var \Text_LanguageDetect
     */
    protected $lang_detect;

    /**
     * @var array
     */
    protected $detectable_langs;

    /**
     * @var bool
     */
    protected $has_jpn = false;

    /**
     * @var array
     */
    protected $jpn_data;

    /**
     * @var int
     */
    protected $jpn_common_word_threshold = 1;

    /**
     * @param \Application\DeskPRO\DependencyInjection\SystemServices\LanguageDataService $lang_data
     */
    public function __construct(LanguageDataService $lang_data)
    {
        $this->lang_data = $lang_data;
    }

    /**
     * @param string $string
     *
     * @return string
     */
    public function detectLanguageCode($string)
    {
        $d = $this->getLanguageDetect();

        if (!$this->detectable_langs) {
            return;
        }

        $string        = strip_tags($string);
        $detected_code = null;

        if (!$detected_code && $this->has_jpn) {
            $count = 0;
            foreach ($this->getCommonJapaneseWords() as $word) {
                if (strpos($string, $word) !== false) {
                    ++$count;

                    if ($count >= $this->jpn_common_word_threshold) {
                        break;
                    }
                }
            }

            if ($count >= $this->jpn_common_word_threshold) {
                $detected_code = 'jpn';
            }
        }

        if (!$detected_code) {
            $detected_code = $d->detectSimple($string);
        }

        return $detected_code ? $detected_code : null;
    }

    /**
     * @param string $string
     *
     * @return \Application\DeskPRO\Entity\Language
     */
    public function detectLanguage($string)
    {
        $code = $this->detectLanguageCode($string);
        if (!$code) {
            return;
        }

        $lang = $this->lang_data->findLangCode($code);

        return $lang;
    }

    /**
     * @return \Text_LanguageDetect
     */
    public function getLanguageDetect()
    {
        if ($this->lang_detect !== null) {
            return $this->lang_detect;
        }

        $this->lang_detect = new \Text_LanguageDetect();
        $this->lang_detect->setNameMode(2);

        $this->detectable_langs = [];
        foreach ($this->lang_data->getLocaleCodes() as $code) {
            if ($this->lang_detect->languageExists($code)) {
                $this->detectable_langs[$code] = $code;
            } elseif ($code == 'ja') {
                $this->has_jpn = true;
            }
        }

        $this->lang_detect->omitLanguages($this->detectable_langs, true);

        return $this->lang_detect;
    }

    /**
     * @return string[]
     */
    public function getDetectableLanguages()
    {
        $this->getLanguageDetect();

        if ($this->has_jpn) {
            return array_merge($this->detectable_langs, ['ja']);
        }

        return $this->detectable_langs;
    }

    /**
     * @return array
     */
    public function getCommonJapaneseWords()
    {
        if ($this->jpn_data) {
            return $this->jpn_data;
        }

        $this->jpn_data = require __DIR__.'/data/japanese-common-words.php';

        return $this->jpn_data;
    }

    /**
     * @param int $threshold
     */
    public function setCommonJapaneseWordThreshold($threshold = 3)
    {
        $this->jpn_common_word_threshold = $threshold;
    }
}
