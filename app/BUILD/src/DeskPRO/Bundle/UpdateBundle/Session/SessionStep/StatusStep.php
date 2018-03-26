<?php

namespace DeskPRO\Bundle\UpdateBundle\Session\SessionStep;

class StatusStep extends SessionStep
{
    protected function init()
    {
        $this->data = array_merge($this->data, [
            'requiresUpdate' => false,
        ]);
    }

    /**
     * @return $this
     */
    public function setRequiresUpdate()
    {
        $this->data['requiresUpdate'] = true;

        return $this;
    }

    /**
     * @return bool
     */
    public function doesRequireUpdate()
    {
        return (bool) $this->data['requiresUpdate'];
    }
}
