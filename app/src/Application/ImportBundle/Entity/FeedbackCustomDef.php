<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

namespace Application\ImportBundle\Entity;

/**
 * Exporting feedback custom def entity.
 *
 * Class FeedbackCustomDef
 */
final class FeedbackCustomDef extends AbstractCustomDef
{
    /**
     * @var string
     */
    protected $sys_name;

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return self::TYPE_FEEDBACK_CUSTOM_DEF;
    }

    /**
     * Returns sys name.
     *
     * @return string
     */
    public function getSysName()
    {
        return $this->sys_name;
    }

    /**
     * Set sys name.
     *
     * @param string $sys_name
     *
     * @return $this
     */
    public function setSysName($sys_name)
    {
        $this->sys_name = $sys_name;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return array_merge(parent::toArray(), array(
            'sys_name' => $this->sys_name,
        ));
    }
}
