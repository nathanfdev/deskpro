<?php
/**************************************************************************\
 * | DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
 * | a British company located in London, England.                            |
 * |                                                                          |
 * | All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
 * |                                                                          |
 * | The license agreement under which this software is released              |
 * | can be found at http://www.deskpro.com/license                           |
 * |                                                                          |
 * | By using this software, you acknowledge having read the license          |
 * | and agree to be bound thereby.                                           |
 * |                                                                          |
 * | Please note that DeskPRO is not free software. We release the full       |
 * | source code for our software because we trust our users to pay us for    |
 * | the huge investment in time and energy that has gone into both creating  |
 * | this software and supporting our customers. By providing the source code |
 * | we preserve our customers' ability to modify, audit and learn from our   |
 * | work. We have been developing DeskPRO since 2001, please help us make it |
 * | another decade.                                                          |
 * |                                                                          |
 * | Like the work you see? Think you could make it better? We are always     |
 * | looking for great developers to join us: http://www.deskpro.com/jobs/    |
 * |                                                                          |
 * | ~ Thanks, Everyone at Team DeskPRO                                       |
 * \**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage
 */

namespace Application\FormBundle\Collection;

use Doctrine\Common\Collections\ArrayCollection;
use Application\DeskPRO\Entity\CustomDataAbstract;
use Doctrine\Common\Collections\Collection;

/**
 * Exactly the same as a doctrine ArrayCollection, except the offsets are always the
 */
class CustomDataCollection implements \ArrayAccess
{
    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    private $custom_datas;

    /**
     * @var object
     */

    private $wants_signal;

    /**
     * @var string
     */
    private $signal;

    /**
     * @param ArrayCollection $custom_datas
     * @param object          $wants_signal the "object" that needs to report to doctrine its change
     * @param string          $signal       the "property" affected by this update
     */
    public function __construct(Collection $custom_datas, $wants_signal, $signal = 'custom_data')
    {
        $this->custom_datas = $custom_datas;
        $this->wants_signal = $wants_signal;
        $this->signal = $signal;
    }

    public function offsetSet($id, $value)
    {
        /** @var CustomDataAbstract $data */
        foreach ($this->custom_datas as $real_offset => $data) {
            if ($id == $data->getFieldId()) {
                $this->custom_datas->set($real_offset, $value);
                $this->wants_signal->_onPropertyChanged($this->signal, null, $this->custom_datas);
                return null;
            }
        }

        if (!$value) {
            return null;
        }

        // can't update, so adding it as new
        $this->custom_datas->add($value);
        $this->wants_signal->_onPropertyChanged($this->signal, null, $this->custom_datas);
    }

    public function offsetGet($id)
    {
        /** @var CustomDataAbstract $data */
        foreach ($this->custom_datas as $data) {
            if ($id == $data->getFieldId()) {
                return $data;
            }
        }
    }

    public function offsetExists($offset)
    {
        return (bool) $this->offsetGet($offset);
    }

    public function offsetUnset($offset)
    {
        /** @var CustomDataAbstract $data */
        foreach ($this->custom_datas as $real_offset => $data) {
            if ($offset == $data->getFieldId()) {
                $this->custom_datas->remove($real_offset);
                return;
            }
        }
    }
}
