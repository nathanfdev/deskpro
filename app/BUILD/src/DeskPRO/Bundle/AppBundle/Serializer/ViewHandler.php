<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AppBundle\Serializer;

use DeskPRO\Bundle\AppBundle\Serializer\Annotation\SerializerView;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ViewHandler.
 */
class ViewHandler extends \FOS\RestBundle\View\ViewHandler
{
    /**
     * @var \FOS\RestBundle\Controller\Annotations\View
     */
    protected $annotation;

    /**
     * {@inheritdoc}
     */
    public function handle(View $view, Request $request = null)
    {
        // I know, I know...
        if (null === $request) {
            $request = $this->container->has('request_stack')
                ? $this->container->get('request_stack')->getCurrentRequest()
                : $this->container->get('request');
        }
        $this->annotation = $request->attributes->get('_view');

        return parent::handle($view, $request);
    }

    /**
     * @param View $view
     *
     * @return SideloadSerializationContext
     */
    protected function getSerializationContext(View $view)
    {
        $context = SideloadSerializationContext::createContext($this->container);

        if ($this->annotation) {
            $groups = $this->annotation->getSerializerGroups();
            if ($groups) {
                $context->setGroups(array_merge($groups, ['wrapper']));
            }
            if ($this->annotation instanceof SerializerView) {
                $context->setMapping($this->annotation->getMapping());
            }
        }

        if ($context->attributes->get('version')->isEmpty() && $this->exclusionStrategyVersion) {
            $context->setVersion($this->exclusionStrategyVersion);
        }

        if (null === $context->shouldSerializeNull() && null !== $this->serializeNullStrategy) {
            $context->setSerializeNull($this->serializeNullStrategy);
        }

        return $context;
    }
}
