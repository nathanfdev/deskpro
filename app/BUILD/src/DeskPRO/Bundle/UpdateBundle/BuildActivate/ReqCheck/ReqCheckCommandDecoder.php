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

namespace DeskPRO\Bundle\UpdateBundle\BuildActivate\ReqCheck;

class ReqCheckCommandDecoder
{
    /**
     * @param string $commandOutput
     *
     * @throws ReqCheckException
     *
     * @return array
     */
    public function decodeResults($commandOutput)
    {
        if (!preg_match('#\-{10,}BEGIN\-{10,}(.*?)\-{10,}END\-{10,}#s', $commandOutput, $match)) {
            throw ReqCheckException::createCommandException('Requirements checker returned unexpected output');
        }

        $results = @json_decode(trim($match[1]), true);

        if (!is_array($results)) {
            throw ReqCheckException::createCommandException('Requirements checker returned invalid output that could not be decoded');
        }

        if (!isset($results['failed_requirements']) || !isset($results['failed_recommendations'])) {
            throw ReqCheckException::createCommandException('Requirements checker returned unexpected results');
        }

        return $results;
    }
}
