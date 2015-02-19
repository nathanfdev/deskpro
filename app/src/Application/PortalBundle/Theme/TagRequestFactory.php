<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
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

namespace Application\PortalBundle\Theme;

use Application\DeskPRO\Domain\DomainObject;
use Application\PortalBundle\Request\TagRequest;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TagRequestFactory
{
    /**
     * @var RequestStack
     */
    private $stack;

    public function __construct(RequestStack $stack)
    {
        $this->stack = $stack;
    }

    public function create(Tag $tag, array $arguments = array())
    {
        $current_request = $this->stack->getCurrentRequest();

        $tr = new TagRequest($this->makeQuery($tag, $arguments), array(), $this->makeAttributes($tag, $arguments));

        $tr->setOptionsResolver(new OptionsResolver());
        $tr->setSession($current_request->getSession());
        $tr->headers->replace($current_request->headers->all());

        return $tr;
    }

    /**
     * @param Tag $tag
     * @param array $arguments
     * @return array
     */
    private function makeQuery(Tag $tag, array $arguments)
    {
        $new_args = array();
        foreach ($arguments as $key => $value) {
            if ($value instanceof DomainObject) {
                $value = $value->getId();
            }

            if (is_object($value)) {
                continue;
            }

            $new_args[$key] = $value;
        }


        $tag_options = array_merge($tag->getDefaultOptions(), array_merge($new_args, array('_tag_name' => $tag->getName())));

        return array('tag_options' => $tag_options);
    }

    /**
     * @param Tag $tag
     * @param array $arguments
     * @return array
     */
    private function makeAttributes(Tag $tag, array $arguments)
    {
        $current_attributes = $this->stack->getCurrentRequest()->attributes->all();

        $new_attributes = array();

        $forbidden_attributes = array('tag_request');

        foreach ($current_attributes as $attr => $val) {
            if (
                '_' === substr($attr, 0, 1)
                || in_array($attr, $forbidden_attributes)
            ) {
                if (!in_array($attr, array('_route', '_route_params'))) {
                    continue;
                }

                if (!$tag->allowRouteParams()) {
                    continue;
                }
            }

            $new_attributes[$attr] = $val;
        }

        return array_merge($new_attributes, array('_tag_name' => $tag->getName()));
    }
}
