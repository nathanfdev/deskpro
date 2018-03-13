<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\ImportBundle\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class ProgressEvent.
 */
class ProgressEvent extends Event
{
    const INIT              = 'importer.init';
    const PRE_IMPORT        = 'importer.pre_import';
    const PRE_MODEL_IMPORT  = 'importer.pre_model_import';
    const POST_MODEL_IMPORT = 'importer.post_model_import';
    const POST_IMPORT       = 'importer.post_import';
    const PRE_APPLY         = 'importer.pre_apply';
    const PRE_BATCH_APPLY   = 'importer.pre_batch_apply';
    const POST_BATCH_APPLY  = 'importer.post_batch_apply';
    const POST_APPLY        = 'importer.post_apply';
    const FINISH            = 'importer.finish';
    const CLEAN             = 'importer.clean';

    /**
     * @var string
     */
    private $modelClass;

    /**
     * @var mixed
     */
    private $options;

    /**
     * Constructor.
     *
     * @param string $modelClass
     * @param mixed  $options
     */
    public function __construct($modelClass = null, $options = null)
    {
        $this->modelClass = $modelClass;
        $this->options    = $options;
    }

    /**
     * @return string
     */
    public function getModelClass()
    {
        return $this->modelClass;
    }

    /**
     * @return mixed
     */
    public function getOptions()
    {
        return $this->options;
    }
}
