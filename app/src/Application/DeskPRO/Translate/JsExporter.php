<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
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

namespace Application\DeskPRO\Translate;

use Orb\Util\Arrays;

class JsExporter
{
    /**
     * @var \Application\DeskPRO\Translate\Translate $tr
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
     * @param  array  $phrase_ids
     * @return string
     */
    public function exportToJson(array $phrase_ids)
    {
        $phrases = $this->tr->getArrayPhraseTexts($phrase_ids);
        $phrases = Arrays::removeValue($phrases, null, true);

        return json_encode($phrases);
    }
}
