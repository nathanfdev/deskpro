<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Languages;

use Orb\Util\DOMDocument;

class GenLanguagePackFile
{
    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $locale;

    /**
     * @var string
     */
    protected $lang_code;

    /**
     * @var string[]
     */
    protected $phrases;

    public function __construct($title, $locale, $lang_code, $phrases = [])
    {
        $this->title     = $title;
        $this->locale    = $locale;
        $this->lang_code = $lang_code;
        $this->phrases   = $phrases;
    }

    /**
     * @param string $id
     * @param string $phrase
     */
    public function addPhrase($id, $phrase)
    {
        $this->phrases[$id] = $phrase;
    }

    /**
     * Add an array of phrases.
     *
     * @param string[] $phrases
     */
    public function addPhrases(array $phrases)
    {
        $this->phrases = array_merge($this->phrases, $phrases);
    }

    /**
     * Get the generated document as a string.
     *
     * @return string
     */
    public function getXml()
    {
        $dom = $this->getDomDocument();

        return $dom->saveXML();
    }

    /**
     * Write the generated XML document to a file.
     *
     * @param string $path
     *
     * @throws \RuntimeException
     */
    public function writeXml($path)
    {
        $xml = $this->getXml();

        if (!file_put_contents($path, $xml)) {
            throw new \RuntimeException('Failed to write XML to file');
        }
    }

    /**
     * Get the generated DOMDocuemtn.
     *
     * @return \DOMDocument
     */
    public function getDomDocument()
    {
        $dom               = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;

        $pack = $dom->createElement('pack');
        $dom->appendChild($pack);

        $lang = $dom->createElement('language');
        $pack->appendChild($lang);

        //------------------------------
        // <language> header
        //------------------------------

        $title = $dom->createElement('title');
        $title->appendChild($dom->createTextNode($this->title));
        $lang->appendChild($title);

        $locale = $dom->createElement('locale');
        $locale->appendChild($dom->createTextNode($this->locale));
        $lang->appendChild($locale);

        $locale = $dom->createElement('lang');
        $locale->appendChild($dom->createTextNode($this->lang));
        $lang->appendChild($locale);

        //------------------------------
        // <phrases>
        //------------------------------

        $phrases = $dom->createElement('phrases');
        $pack->appendChild($phrases);

        ksort($this->phrases, \SORT_STRING);

        foreach ($this->phrases as $id => $phrase) {
            $p = $dom->createElement('phrase');
            $p->appendChild($dom->createTextNode($phrase));
            $p->setAttribute('id', $id);
            $phrases->appendChild($p);
        }

        return $dom;
    }
}
