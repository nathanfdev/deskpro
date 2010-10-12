COPY FILES
================================================================================

The files in the `files` directory should all be copied over to the `library`
directory within your XenForo instance.


SET PATH TO DESKPRO
================================================================================

Edit your XenForo `config.php` file to add the following line:

	$config['DeskPRO']['rootDir'] = '/path/to/appfiles';

Note that if XenForo is installed on a separate machine than DeskPRO, you should
copy ovew the following directories:

- `appfiles/src/DeskPRO`
- `appfiles/src/Orb`
- `appfiles/vendor`

Only these source files are necessary. Config files, cache directories etc
are not used and can be ignored.


INSTALL ADDONS
================================================================================

Install the addons located in the `addons` directory by uploading them from
within the XenForo admin interface.