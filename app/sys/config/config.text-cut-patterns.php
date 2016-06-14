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

return array(
    # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    # This file should not be edited directly. If you want
    # to add custom patterns, create a new file named
    # config.text-cut-patterns.php in the same directory
    # as your config.php file.
    # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    'outlook_1' => 'lang:#^(\-\-\-\-\-\s*Original Message\s*\-\-\-\-\-)?\s%From%: (?P<from>.*?)\s+%Sent%: (.*?)\s+%To%: (.*?)\s+(%CC%: (.*?)\s+)?%Subject%: (.*?)\s+#im',

    'sparrow_1' => '#^On (.*?), ([0-9]+) (.*?) ([0-9]{4})( at (.*?))?, (?P<from>.*?) wrote:$#m',

    'thunderbird_1'    => '#^On ([0-9]+)/([0-9]+)/([0-9]+) ([0-9]{1,2}):([0-9]{1,2})\s*([ap]m)?, (?P<from>.*?) wrote:#mi',
    'thunderbird_2'    => '#^\-\-\- Original Message Follows \-\-\-\s+Sender:(?P<from>.*?)\s+Date:(.*?)\s+#m',
    'thunderbird_3'    => '#^(?P<from>.*?) wrote on \d+\.\d+\.\d+ \d+:\d+\s*:\s*$#m',
    'thunderbird_fr_1' => '#^Le (.*?), (?P<from>.*?) a écrit\s*:#m',

    'applemail_1' => '#^On (.*?), ([0-9]+), at (.*?), (?P<from>.*?) wrote:\s+#m',

    'zimbra_1' => '#^\-\-\-\-\-\s+Original Message\s+\-\-\-\-\-\sFrom: (?P<from>.*?)\sTo: (.*?)#m',

    'generic_1'    => '#^[a-zA-Z0-9\-\.\'" ]+ (?P<from><.*?@[a-zA-Z0-9\.\-_]+>) wrote:\s$#m',
    'generic_2'    => '#^El (.*?) a las (.*?) (?P<from>.*?) escribi(ó|o):\s$#m',
    'generic_3'    => '#^On .*? (?P<from><.*?@[a-zA-Z0-9\.\-_]+>) wrote:\s*$#m',
    'generic_4'    => '#^On .*? (?P<from><.*?@[a-zA-Z0-9\.\-_]+<.*?@[a-zA-Z0-9\.\-_]+>>) wrote:\s*$#m',
    'generic_5_it' => '#^Il .*? ha scritto:\s*$#m',

    'generic_6' => '#^[^>]{1}.*? wrote on [0-9/]+ [0-9:]+( (am|AM|pm|PM))?:#im',
    'generic_7' => '#^[^>]{1}.*? wrote:\n>#m',

    'mutt' => '#^On \d{4}\-\d{2}\-\d{2} \d{2}:\d{2}, (?P<from>.*?) wrote:#im',

    'claws' => '#^On \w+, \d+ \w+, (?P<from>.*?) wrote:#im',
);
