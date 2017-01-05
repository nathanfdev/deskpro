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

namespace Application\DeskPRO\Routing\Matcher\Dumper;

use Symfony\Component\Routing\Matcher\Dumper\PhpMatcherDumper as BasePhpMatcherDumper;

class PhpMatcherDumper extends BasePhpMatcherDumper
{
    public function dump(array $options = [])
    {
        $options['base_class'] = 'Symfony\\Bundle\\FrameworkBundle\\Routing\\RedirectableUrlMatcher';

        $dump = parent::dump($options);
        $dump = str_replace('public function match($pathinfo)', 'protected function doMatch($pathinfo)', $dump);

        $add_match = <<<'EOF'
public function match($pathinfo)
    {
        try {
            return $this->doMatch($pathinfo);
        } catch (ResourceNotFoundException $e) {
            // Try without trailing
            if (substr($pathinfo, -1) == '/') {
                $pathinfo = rtrim($pathinfo, '/');
                $match = $this->doMatch($pathinfo);

                return $this->redirect($pathinfo, $match['_route']);
            // Try with trailing slash
            } else {
                $pathinfo = $pathinfo . '/';
                $match = $this->doMatch($pathinfo);

                return $this->redirect($pathinfo, $match['_route']);
            }
        }
    }
EOF;

        $dump = str_replace('protected function doMatch(', "$add_match\n\n\tprotected function doMatch(", $dump);

        return $dump;
    }
}
