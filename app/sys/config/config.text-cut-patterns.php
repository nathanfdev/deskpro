<?php return array(
	# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	# This file should not be edited directly. If you want
	# to add custom patterns, create a new file named
	# config.text-cut-patterns.php in the same directory
	# as your config.php file.
	# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	'outlook_1'                          => 'lang:#^(\-\-\-\-\-\s*Original Message\s*\-\-\-\-\-)?\s%From%: (.*?)\s+%Sent%: (.*?)\s+%To%: (.*?)\s+(%CC%: (.*?)\s+)?%Subject%: (.*?)\s+#im',

	'sparrow_1'                          => '#^On (.*?), ([0-9]+) (.*?) ([0-9]{4})( at (.*?))?, (.*?) wrote:$#m',

	'thunderbird_1'                      => '#^On ([0-9]+)/([0-9]+)/([0-9]+) ([0-9]{1,2}):([0-9]{1,2})\s*([ap]m)?, (.*?) wrote:#mi',
	'thunderbird_2'                      => '#^\-\-\- Original Message Follows \-\-\-\s+Sender:(.*?)\s+Date:(.*?)\s+#m',
	'thunderbird_fr_1'                   => '#^Le (.*?), (.*?) a écrit\s*:#m',

	'applemail_1'                        => '#^On (.*?), ([0-9]+), at (.*?), (.*?) wrote:\s+#m',

	'zimbra_1'                           => '#^\-\-\-\-\-\s+Original Message\s+\-\-\-\-\-\sFrom: (.*?)\sTo: (.*?)#m',

	'generic_1'                          => '#^[a-zA-Z0-9\-\.\'" ]+ <.*?@[a-zA-Z0-9\.\-_]+> wrote:\s$#m',
	'generic_2'                          => '#^El (.*?) a las (.*?) (.*?) escribi(ó|o):\s$#m',
	'generic_3'                          => '#^On .*? <.*?@[a-zA-Z0-9\.\-_]+> wrote:\s*$#m',
	'generic_4'                          => '#^On .*? <.*?@[a-zA-Z0-9\.\-_]+<.*?@[a-zA-Z0-9\.\-_]+>> wrote:\s*$#m',
	'generic_5_it'                       => '#^Il .*? ha scritto:\s*$#m',

	'generic_6'                          => '#^[^>]{1}.*? wrote on [0-9/]+ [0-9:]+( (am|AM|pm|PM))?:#im',
	'generic_7'                          => '#^[^>]{1}.*? wrote:\n>#m',

	'mutt'                               => '#^On \d{4}\-\d{2}\-\d{2} \d{2}:\d{2}, (.*?) wrote:#im',
);