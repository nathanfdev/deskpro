<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\TicketLayout;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\TicketLayout\Terms\TicketLayoutTermInterface;
use Orb\Util\CheckedOptionsArray;
use Orb\Util\Strings;

/**
 * Class LayoutFieldCriteria.
 */
class LayoutFieldCriteria implements \Serializable, \Countable
{
    const CRIT_ALL = 'all';
    const CRIT_ANY = 'any';

    /**
     * @var string
     */
    private $mode = self::CRIT_ALL;

    /**
     * @var \Application\DeskPRO\TicketLayout\Terms\TicketLayoutTermInterface[]
     */
    private $terms = [];

    /**
     * @param \Application\DeskPRO\TicketLayout\Terms\TicketLayoutTermInterface[] $terms
     * @param string                                                              $mode
     */
    public function __construct(array $terms = null, $mode = self::CRIT_ALL)
    {
        $this->setMode($mode);

        if ($terms) {
            foreach ($terms as $t) {
                $this->addTerm($t);
            }
        }
    }

    /**
     * @param TicketLayoutTermInterface $term
     */
    public function addTerm(TicketLayoutTermInterface $term)
    {
        $this->terms[] = $term;
    }

    /**
     * @return \Application\DeskPRO\TicketLayout\Terms\TicketLayoutTermInterface[]
     */
    public function getTerms()
    {
        return $this->terms;
    }

    /**
     * @return string
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * @param string $mode
     */
    public function setMode($mode)
    {
        $mode       = strtolower($mode);
        $this->mode = ($mode == self::CRIT_ALL ? self::CRIT_ALL : self::CRIT_ANY);
    }

    /**
     * @param Ticket $ticket
     *
     * @return bool
     */
    public function isTicketMatch(Ticket $ticket)
    {
        if ($this->mode == self::CRIT_ALL) {
            foreach ($this->terms as $t) {
                if (!$t->isTicketMatch($ticket)) {
                    return false;
                }
            }

            return true;
        } else {
            foreach ($this->terms as $t) {
                if ($t->isTicketMatch($ticket)) {
                    return true;
                }
            }

            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function compileJsCheck()
    {
        if (!$this->terms) {
            return 'function () { return true; }';
        }

        $js = "(function () {\n";
        $js .= "\tvar checkFn = [\n";
        $fn_bits = [];
        foreach ($this->terms as $t) {
            $t_js      = $t->compileJsCheck();
            $t_js      = trim(Strings::modifyLines($t_js, "\t\t"));
            $fn_bits[] = "\t\t$t_js";
        }
        $js .= implode(",\n", $fn_bits);
        $js .= "\n\t];\n";

        $js .= "\treturn function (ticket) {\n";
        $js .= "\t\tfor(var i = 0; i < checkFn.length; i++) { ";
        if ($this->mode == self::CRIT_ANY) {
            $js .= 'if (checkFn[i](ticket)) return true;';
        } else {
            $js .= 'if (!checkFn[i](ticket)) return false;';
        }
        $js .= " }\n";

        if ($this->mode == self::CRIT_ANY) {
            $js .= "\t\treturn false;\n";
        } else {
            $js .= "\t\treturn true;\n";
        }

        $js .= "\t};\n";
        $js .= '})()';

        return $js;
    }

    /**
     * @return array
     */
    public function exportToArray()
    {
        $data = [];

        $data['version'] = 1;
        $data['mode']    = $this->mode;
        $data['terms']   = [];

        foreach ($this->terms as $t) {
            if (($options = $t->getTermOptions()) instanceof CheckedOptionsArray) {
                /** @var CheckedOptionsArray $options */
                $options = $options->all();
            }
            $data['terms'][] = [
                'type'    => $t->getTermType(),
                'op'      => $t->getTermOperator(),
                'options' => $options,
            ];
        }

        return $data;
    }

    /**
     * @return string
     */
    public function exportToJson()
    {
        return json_encode($this->exportToArray());
    }

    /**
     * @param array $data
     */
    public function importFromArray(array $data)
    {
        $this->setMode($data['mode']);

        foreach ($data['terms'] as $t) {
            $classname = 'Application\\DeskPRO\\TicketLayout\\Terms\\'.$t['type'];
            $obj       = new $classname($t['op'], $t['options']);
            $this->addTerm($obj);
        }
    }

    /**
     * @return string
     */
    public function serialize()
    {
        return $this->exportToJson();
    }

    /**
     * @param string $data
     */
    public function unserialize($data)
    {
        $data = json_decode($data, true);

        $this->__construct($data['field_type'], $data['field_id']);
        $this->importFromArray($data);
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->terms);
    }
}
