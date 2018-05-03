<?php

return [
    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    // This file should not be edited directly. If you want
    // to add custom patterns, create a new file named
    // config.text-cut-patterns.php in the same directory
    // as your config.php file.
    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

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
];
