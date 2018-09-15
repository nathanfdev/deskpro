<?php

namespace Application\DeskPRO\NewSearch\Manager\Traits;

use DeskPRO\Bundle\PortalBundle\Routing\UrlMatcher;
use League\Url\Url;
use Orb\Util\Numbers;
use Orb\Util\Strings;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

/**
 * Trait ExtractsMatchersFromQuery
 * @package Application\DeskPRO\NewSearch\Manager\Traits
 */
trait ExtractsMatchersFromQuery
{
    /**
     * Permalinks, links with slugs and ticket links with refcodes
     *
     * Articles:
     * - Permalink with ID (agent) : http://support.deskpro.com/agent/go/article/4
     * - Permalink with SLUG (user): http://support.deskpro.com/en/kb/articles/amet-quod-non-veritatis-voluptas
     * - Permalink with ID (user)  : http://support.deskpro.com/en/kb/articles/4
     *
     * Chats:
     * - Permalink with ID (agent) : http://support.deskpro.com/agent/go/chat/20
     *
     * Downloads:
     * - Permalink with ID (agent) : http://support.deskpro.com/agent/go/download/6
     * - Permalink with SLUG (user): http://support.deskpro.com/en/downloads/files/sint-qui-id-cum-vel
     * - Permalink with ID (user)  : http://support.deskpro.com/en/downloads/files/6
     *
     * Feedbacks:
     * - Permalink with ID (agent) : http://support.deskpro.com/agent/go/feedback/4
     * - Permalink with SLUG (user): http://support.deskpro.com/en/feedback/view/ut-ut-et-at-in
     * - Permalink with ID (user)  : http://support.deskpro.com/en/feedback/view/4
     *
     * News:
     * - Permalink with ID (agent) : http://support.deskpro.com/agent/go/news/1
     * - Permalink with SLUG (user): http://support.deskpro.com/en/news/posts/example-news-post
     * - Permalink with ID (user)  : http://support.deskpro.com/en/news/posts/1
     *
     * Organizations:
     * - Permalink with ID (agent) : http://support.deskpro.com/agent/go/organization/1
     *
     * People:
     * - Permalink with ID (agent) : http://support.deskpro.com/agent/go/person/1
     *
     * Tickets:
     * - Permalink with ID (agent) : http://support.deskpro.com/agent/go/ticket/524
     * - Permalink with REF (agent): http://support.deskpro.com/agent/go/ticket/54ZZ0HLYO5EEPWB
     * - Permalink with REF (user) : http://support.deskpro.com/en/tickets/54ZZ0HLYO5EEPWB
     * - Permalink with ID (user)  : http://support.deskpro.com/en/tickets/524
     */

    /**
     * Fragments for opened object
     *
     * Pairs of fragment code -> object:
     * a.o => article
     * http://support.deskpro.com//agent/#app.publish,knowledgebase:1,i:17,a.o:1,vis:7
     * c.o => chat
     * http://support.deskpro.com/agent/#app.userchat,ended:mine:,c.o:20,vis:7
     * d.o => download
     * http://support.deskpro.com//agent/#app.publish,downloads:1,i:17,a:1,n:1,d.o:7,vis:7
     * i.o => feedback
     * http://support.deskpro.com//agent/#app.feedback,fb_content,i.o:17,vis:7
     * n.o => news
     * http://support.deskpro.com//agent/#app.publish,news:1,i:17,a:1,n.o:1,vis:7
     * o.o => organization
     * http://support.deskpro.com/agent/#app.people,orgs,o.o:36,vis:7
     * p.o => person
     * http://support.deskpro.com/agent/#app.people,people:*,p.o:52,vis:7
     * t.o => ticket
     * http://support.deskpro.com/agent/#app.tickets,t.o:524,vis:7
     *
     * @see $objectFragmentMap
     */

    /**
     * @param string $query
     * @return array
     */
    protected function extractMatchersFromQuery($query)
    {
        // Map gragment part to object
        $matcherFragmentMap = [
            't' => 'ticket',
            'a' => 'article',
            'n' => 'news',
            'd' => 'download',
            'i' => 'feedback',
            'c' => 'chat',
            'p' => 'person',
            'o' => 'organization'
        ];

        // stub
        $matchers = [];

        // Try to parse an URL, in case it's provided as a search query
        try {
            // all cases: we do not care about the host, we just need to check if could get object identifier
            // so just attempt to parse URL. Unfortunately, the old League\Url is dump and can't validate the URL
            // so just catch \RuntimeException in case of parse failure.
            $url = Url::createFromUrl($query);

            // search the framgment first
            // eg.: https://support.deskpro.com/agent/#app.tickets,inbox:agent,t:109346,t:108966,t:108169,p.o:65264,vis:7
            $fragment = $url->getFragment()->get();
            if (! is_null($fragment)) {
                $keys = implode('|', array_keys($matcherFragmentMap));
                if (false !== (bool) preg_match_all("/[{$keys}]\.o:\d*/", $fragment, $matches)) {
                    foreach (array_unique($matches[0]) as $match) {
                        list($mapper, $id) = explode(':', $match);
                        $matchers[] = [
                            'object' => $matcherFragmentMap[explode('.', $mapper)[0]],
                            'param'  => $id,
                            'field'  => 'id',
                        ];
                    }
                }
            }

            /**
             * case 2: search object id/ref/slug in path.
             * Eg.:
             * - http://support.deskpro.com/agent/go/ticket/524
             * - http://support.deskpro.com/en/tickets/54ZZ0HLYO5EEPWB
             * - http://support.deskpro.com/en/feedback/view/ut-ut-et-at-in
             * - etc.
             */
            $router = $this->container->get('dp.dynamic_context_router');
            $rwpath = $url->getPath();
            $clpath = (new UrlMatcher())->extractLanguageCode('/'.$rwpath->get())['remaining_pathinfo'];

            // agent permalinks (/agent/go/<object>/<param>)
            if (false !== strpos($clpath, 'agent/go')) {
                try {
                    $params  = $router->match($clpath);
                    foreach ($params as $key => $val) {
                        if (! Strings::startsWith('_', $key)) {
                            $matchers[] = [
                                'object' => $rwpath->toArray()[2],
                                'field'  => $key,
                                'param'  => $val,
                            ];
                        }
                    }
                } catch (ResourceNotFoundException $exception) {
                }
            }

            // ticket user links
            if (false !== strpos($clpath, 'tickets')) {
                try {
                    $params  = $router->match($clpath);
                    foreach ($params as $key => $val) {
                        if (! Strings::startsWith('_', $key)) {
                            if ($key === 'ticket_ref') {
                                if (Numbers::isInteger($val)) {
                                    $key = 'id';
                                    $val = (int) $val;
                                } else {
                                    $key = 'ref';
                                }
                            }

                            $matchers[] = [
                                'object' => 'ticket',
                                'field'  => $key,
                                'param'  => $val,
                            ];
                        }
                    }
                } catch (ResourceNotFoundException $exception) {
                }
            }

            $matrix = [
                'article'  => 'kb/articles',
                'download' => 'downloads/files',
                'feedback' => 'feedback/view',
                'news'     => 'news/posts',
            ];

            foreach ($matrix as $object => $pattern) {
                if (false !== strpos($rwpath, $pattern)) {
                    try {
                        $params  = $router->match($clpath);
                        foreach ($params as $key => $val) {
                            if (! Strings::startsWith('_', $key)) {
                                if ($key === 'slug' && Numbers::isInteger($val)) {
                                    $key = 'id';
                                    $val = (int) $val;
                                }
                                $matchers[] = [
                                    'object' => $object,
                                    'field'  => $key,
                                    'param'  => $val,
                                ];
                            }
                        }
                    } catch (ResourceNotFoundException $exception) {
                    }
                }
            }
        } catch (\RuntimeException $exception) {
            // do nothing, and just let it go further
        }

        return $matchers;
    }
}
