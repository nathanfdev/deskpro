<?php

namespace DeskPRO\Bundle\ImportBundle\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class ProgressEvent.
 */
class ProgressEvent extends Event
{
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
