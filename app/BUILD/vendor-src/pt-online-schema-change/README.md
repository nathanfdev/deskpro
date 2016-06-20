To use pt-online-schema-change:

1. Install percona-tools the normal way to make sure the server has the required dependenceies like Perl etc.

    https://www.percona.com/doc/percona-toolkit/2.2/index.html

2. Create `/config/config.upgrader.php` and add the following lines to enable our patched tool:

    $CONFIG = [];
    $CONFIG['online_schema_upgrade'] = '%dp.app_dir%/vendor-src/pt-online-schema-change/pt-online-schema-change';

    // optionally use a different user/password
    $CONFIG['online_schema_upgrade_user']     = 'drop_capable_user';
    $CONFIG['online_schema_upgrade_password'] = 'pass';

---

That makes the upgrader use this patched online schema tool. This copy of pt-online-schema-change
includes a patch to fix this bug:

    https://bugs.launchpad.net/percona-toolkit/+bug/1498128

... which fixes a problem to do with FK's being renamed with leading underscores after using the tool
multiple times on the same tables (e.g. say, by multiple upgrade scripts).

!!! IMPORTANT !!!

Note that the behaviour of naming FKs is being discussed in the bug report above. It might change. FK names
are obviously requried to do some operations (e.g. to DROP a FK), and currently execSlowAlterTable will
add/remove the leading underscore as necessary. If the tool is ugpraded at a later date to use some other
mechanism of renaming FK's, then execSlowAlterTable will break.

In other words, execSlowAlterTable is only known to work when using this specific patched version of
pt-online-schema-change.
