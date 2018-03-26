<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Translate;

use Orb\Util\Arrays;

class JsExporter
{
    /**
     * @var \Application\DeskPRO\Translate\Translate
     */
    protected $tr;

    /**
     * @param Translate $tr
     */
    public function __construct(Translate $tr)
    {
        $this->tr = $tr;
    }

    /**
     * @param array $phrase_ids
     */
    public function exportToJsFile($varname, array $phrase_ids)
    {
        $json = $this->exportToJson($phrase_ids);

        if ($varname == 'return') {
            $js = "(function () {\n\nvar lang = $json;\n\nreturn lang;\n\n}).call(this);";
        } elseif ($varname == 'define') {
            $js = "(function () {\ndefine(function () {\n\nvar lang = $json;\n\nreturn lang;\n\n});\n\n}).call(this);";
        } else {
            $js = "$varname = $json;";
        }

        return $js;
    }

    /**
     * @param array $phrase_ids
     *
     * @return string
     */
    public function exportToJson(array $phrase_ids)
    {
        $phrases = $this->tr->getArrayPhraseTexts($phrase_ids);
        $phrases = Arrays::removeValue($phrases, null, true);

        return json_encode($phrases);
    }
}
