<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Languages;

/**
 * A language pack file is simple XML that looks like this:.
 *
 * <pack>
 *     <language id="system_name">
 *         <!-- Human readable title -->
 *         <title>English (US)</locale>
 *
 *         <!-- The default locale to use -->
 *         <locale>en_US</locale>
 *
 *         <!-- The ISO 639-2 language code -->
 *         <lang>eng</lang>
 *     </language>
 *     <phrases>
 *         <phrase id="section.group.id">Phrase contents here</phrase>
 *     </phrases>
 * </pack>
 */
class LanguagePackFile
{
    /**
     * @var \SimpleXMLElement
     */
    protected $xml;

    /**
     * @var LanguagePack
     */
    protected $pack;

    public function __construct(\SimpleXMLElement $xml)
    {
        $this->xml  = $xml;
        $this->pack = new LanguagePack();
    }

    /**
     * @static
     *
     * @param string $source
     *
     * @throws \RuntimeException
     *
     * @return \Application\DeskPRO\Languages\LanguagePackFile
     */
    public static function newFromString($source)
    {
        $xml = simplexml_load_string($source);
        if (libxml_get_last_error() !== false) {
            throw new \RuntimeException('Failed parsing XML source');
        }

        return new self($xml);
    }

    /**
     * @static
     *
     * @param string $source
     *
     * @throws \RuntimeException
     *
     * @return \Application\DeskPRO\Languages\LanguagePackFile
     */
    public static function newFromFile($path)
    {
        $source = file_get_contents($path);

        if (!$source) {
            throw new \RuntimeException('Failed to read source file');
        }

        return self::newFromString($source);
    }

    /**
     * @return string
     */
    public function getSysName()
    {
        if ($this->pack->sys_name !== null) {
            return $this->pack->sys_name;
        }

        $this->pack->sys_name = (string) $this->xml->language['id'];

        return $this->pack->sys_name;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        if ($this->pack->title !== null) {
            return $this->pack->title;
        }

        $this->pack->title = (string) $this->xml->language->title;

        return $this->pack->title;
    }

    /**
     * @return string
     */
    public function getLangCode()
    {
        if ($this->pack->lang_code !== null) {
            return $this->pack->lang_code;
        }

        $this->pack->lang_code = (string) $this->xml->language->lang;

        return $this->pack->lang_code;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        if ($this->pack->locale !== null) {
            return $this->pack->locale;
        }

        $this->pack->locale = (string) $this->xml->language->locale;

        return $this->pack->locale;
    }

    /**
     * @return string[]
     */
    public function getPhrases()
    {
        if ($this->pack->phrases !== null) {
            return $this->pack->phrases;
        }

        $this->pack->phrases = [];

        foreach ($this->xml->phrases->phrase as $node) {
            $id   = (string) $node['id'];
            $text = (string) $node;

            $this->pack->phrases[$id] = $text;
        }

        return $this->pack->phrases;
    }

    /**
     * @return LanguagePack
     */
    public function getPack()
    {
        $this->getSysName();
        $this->getTitle();
        $this->getLocale();
        $this->getLangCode();
        $this->getPhrases();

        return $this->pack;
    }
}
