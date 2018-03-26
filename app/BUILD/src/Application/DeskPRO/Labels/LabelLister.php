<?php

/**
 * DeskPRO.
 *
 * @category ORM
 */

namespace Application\DeskPRO\Labels;

use Application\DeskPRO\App;
use Orb\Util\Strings;

class LabelLister
{
    /** @var string */
    protected $label_type;

    /**
     * @param string $label_type
     */
    public function __construct($label_type)
    {
        $this->label_type = $label_type;
    }

    /**
     * Gets an index of labels by index=>array(lables). THe index is usually the
     * letter, but maybe not depending on language. (?).
     *
     * @return array
     */
    public function getIndexList()
    {
        $index = [];

        $statement = App::getDb()->executeQuery("
            SELECT DISTINCT(label)
            FROM labels_{$this->label_type}
            ORDER BY label ASC
        ");

        while ($row = $statement->fetch(\PDO::FETCH_ASSOC)) {
            $label = $row['label'];

            $first = Strings::utf8_substr($label, 0, 1);
            $first = Strings::utf8_accents_to_ascii($first);
            $first = Strings::utf8_strtoupper($first);

            if (is_numeric($first)) {
                $first = '#';
            } elseif (!preg_match('#[A-Z]#', $first)) {
                $first = '@';
            }

            if (!isset($index[$first])) {
                $index[$first] = [];
            }

            $index[$first][] = $label;
        }

        return $index;
    }
}
