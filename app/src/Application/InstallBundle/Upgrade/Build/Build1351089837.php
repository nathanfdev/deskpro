<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
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
 * @subpackage
 */

namespace Application\InstallBundle\Upgrade\Build;

class Build1351089837 extends AbstractBuild
{
	public function run()
	{
		$this->out("Insert new default triggers to set 'From' name");
		$this->execMutateSql("
			INSERT INTO `ticket_triggers` (`id`, `title`, `event_trigger`, `is_enabled`, `terms`, `actions`, `sys_name`, `run_order`, `date_created`, `event_trigger_options`, `terms_any`)
			VALUES
				(NULL, '', 'update.agent', 1, X'613A303A7B7D', X'613A313A7B693A303B613A323A7B733A343A2274797065223B733A32313A227365745F696E697469616C5F66726F6D5F6E616D65223B733A373A226F7074696F6E73223B613A323A7B733A393A2266726F6D5F6E616D65223B733A31383A227B7B706572666F726D65722E6E616D657D7D223B733A373A22746F5F77686F6D223B733A313A2230223B7D7D7D', NULL, -10, '2012-10-24 14:10:26', X'613A303A7B7D', X'613A303A7B7D'),
				(NULL, '', 'update.user', 1, X'613A303A7B7D', X'613A313A7B693A303B613A323A7B733A343A2274797065223B733A32313A227365745F696E697469616C5F66726F6D5F6E616D65223B733A373A226F7074696F6E73223B613A323A7B733A393A2266726F6D5F6E616D65223B733A31383A227B7B706572666F726D65722E6E616D657D7D223B733A373A22746F5F77686F6D223B733A313A2230223B7D7D7D', NULL, -10, '2012-10-24 14:12:46', X'613A303A7B7D', X'613A303A7B7D'),
				(NULL, '', 'new.email.user', 1, X'613A303A7B7D', X'613A313A7B693A303B613A323A7B733A343A2274797065223B733A32313A227365745F696E697469616C5F66726F6D5F6E616D65223B733A373A226F7074696F6E73223B613A323A7B733A393A2266726F6D5F6E616D65223B733A31383A227B7B706572666F726D65722E6E616D657D7D223B733A373A22746F5F77686F6D223B733A353A226167656E74223B7D7D7D', NULL, -10, '2012-10-24 14:14:05', X'613A303A7B7D', X'613A303A7B7D'),
				(NULL, '', 'new.web.user', 1, X'613A303A7B7D', X'613A313A7B693A303B613A323A7B733A343A2274797065223B733A32313A227365745F696E697469616C5F66726F6D5F6E616D65223B733A373A226F7074696F6E73223B613A323A7B733A393A2266726F6D5F6E616D65223B733A32323A2240407B7B706572666F726D65722E6E616D657D7D4040223B733A373A22746F5F77686F6D223B733A353A226167656E74223B7D7D7D', NULL, -10, '2012-10-24 14:14:05', X'613A303A7B7D', X'613A303A7B7D'),
				(NULL, '', 'new.email.agent', 1, X'613A303A7B7D', X'613A313A7B693A303B613A323A7B733A343A2274797065223B733A32313A227365745F696E697469616C5F66726F6D5F6E616D65223B733A373A226F7074696F6E73223B613A323A7B733A393A2266726F6D5F6E616D65223B733A31383A227B7B706572666F726D65722E6E616D657D7D223B733A373A22746F5F77686F6D223B733A313A2230223B7D7D7D', NULL, -10, '2012-10-24 14:14:05', X'613A303A7B7D', X'613A303A7B7D'),
				(NULL, '', 'new.web.agent.portal', 1, X'613A303A7B7D', X'613A313A7B693A303B613A323A7B733A343A2274797065223B733A32313A227365745F696E697469616C5F66726F6D5F6E616D65223B733A373A226F7074696F6E73223B613A323A7B733A393A2266726F6D5F6E616D65223B733A31383A227B7B706572666F726D65722E6E616D657D7D223B733A373A22746F5F77686F6D223B733A313A2230223B7D7D7D', NULL, -10, '2012-10-24 14:14:05', X'613A303A7B7D', X'613A303A7B7D');
		");
	}
}