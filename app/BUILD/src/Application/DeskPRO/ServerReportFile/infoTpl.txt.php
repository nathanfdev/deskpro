<?php /** @var \Application\DeskPRO\ServerReportFile\InfoTpl $this */
use DpSys\License;

?>
#######################################################################
# Status
#######################################################################

Report Time:          <?php $this->output($this->fullDate($this->genTime)) ?>
Deskpro Build ID:     <?php $this->output($this->version) ?>
Schema ID:            <?php $this->output($this->schemaId) ?>

Install Time:         <?php $this->output($this->fullDate($this->installTime) ?: 'n/a') ?>
Install Schema ID:    <?php $this->output($this->installSchemaId ?: 'n/a') ?>
Install Source:       <?php $this->output($this->installSource ?: 'n/a') ?>


#######################################################################
# License
#######################################################################
<?php
$license          = License::getLicense();
$licenseExpiresIn = function () use ($license) {
    if ($license->getExpireDays() == 0 && $license->getExpireTime('hours') == 0) {
        return 'In '.$license->getExpireTime('mins').' minutes';
    } elseif ($license->getExpireDays() < 3) {
        return 'In '.$license->getExpireTime('hours').' hours';
    } else {
        return 'In '.$license->getExpireDays().' days';
    }
};
?>

License ID: <?php $this->output($license->getLicenseId()) ?>
Agents: <?php $this->output($license->getMaxAgents()) ?>
Expires: <?php $this->output($licenseExpiresIn()) ?>

<?php echo $this->formatLicenseCode($license->getLicenseCode()) ?>


#######################################################################
# Database Configuration
#######################################################################

<?php foreach ($this->getDatabases() as $d) {
    echo "$d\n";
} ?>

Online Schema Upgrade: <?php $this->output($this->getConfig('upgrader.online_schema_upgrade') ? 'ENABLED' : 'off') ?>


#######################################################################
# Env Configuration
#######################################################################

Deskpro Root:    <?php $this->output(DP_ROOT) ?>
Web Root:        <?php $this->output(DP_WEB_ROOT) ?>

Environment:     <?php $this->output($this->getConfig('env.environment')) ?>
Debug Mode:      <?php $this->output($this->getConfig('env.debug_mode') ? 'ENABLED' : 'off') ?>
File umask:      <?php $this->output(sprintf('%o', $this->getConfig('env.set_umask') ?: 0000)) ?>

PHP Path:        <?php $this->output($this->getConfig('paths.php_path') ?: 'n/a/') ?>
MySQL Path:      <?php $this->output($this->getConfig('paths.mysql_path') ?: 'n/a/') ?>
MySQL Dump Path: <?php $this->output($this->getConfig('paths.mysqldump_path') ?: 'n/a/') ?>

<?php if ($assetPaths = $this->getConfig('asset_paths')) {
    foreach ($assetPaths as $name => $path) {
        echo "Asset Path:      [$name] {$path['value']} ({$path['type']})\n";
    }
} else {
    echo "Asset Path:      default\n";
}

if ($proxies = $this->getConfig('trust_proxy_daata')) {
    foreach ($proxies as $p) {
        echo "Trusted Proxy:   $p\n";
    }
} else {
    echo "Trusted Proxy:   none\n";
}

if ($initScripts = $this->getConfig('init_scripts')) {
    foreach ($initScripts as $p) {
        echo "Init Script:     $p\n";
    }
} else {
    echo "Init Script:     none\n";
} ?>

Init Function: <?php $this->output($this->getConfig('init_fn') ? 'CUSTOM' : 'none') ?>
Library Loader Function: <?php $this->output($this->getConfig('load_lib_fn') ? 'CUSTOM' : 'none') ?>
Interesting Event Function: <?php $this->output($this->getConfig('interesting_events_fn') ? 'CUSTOM' : 'none') ?>


#######################################################################
# Overrides
#######################################################################

Portal HTTP Cache: <?php $this->output($this->getConfig('disable_portal_http_cache') ? 'DISABLED' : 'on') ?>
URL Corrections: <?php $this->output($this->getConfig('disable_url_corrections') ? 'DISABLED' : 'on') ?>
Outgoing Email: <?php $this->output($this->getConfig('disable_outgoing_email') ? 'DISABLED' : 'on') ?>

<?php foreach ($this->getConfig('settings') as $name => $value) {
    echo "$name: ".(is_scalar($value) ? $value : json_encode($value))."\n";
} ?>


#######################################################################
# Cron
#######################################################################

Last Cron Start:      <?php $this->output($this->fullDate($this->lastCronStartTime) ?: 'n/a') ?>
Last Cron Complete:   <?php $this->output($this->fullDate($this->lastCronTime) ?: 'n/a') ?>

<?php
$format = "%-30s  %-4s  %-16s  %-16s\n";
printf($format, 'Job', 'Int.', 'Last Run', 'Next Run');
printf($format, str_repeat('=', 30), str_repeat('=', 4), str_repeat('=', 16), str_repeat('=', 16));

foreach ($this->getCronInfo() as $j) {
    printf(
        $format,
        $j->id,
        $j->getIntervalReadable(),
        $j->last_run_date ? \Orb\Util\Dates::dateToAgo($j->last_run_date, 3, 'short') : 'Never',
        $j->next_run_date ? \Orb\Util\Dates::dateToAgo($j->next_run_date, 3, 'short') : 'NA'
    );
}
?>

#######################################################################
# Table Counts
#######################################################################

<?php foreach ($this->getTableCounts() as $t => $c) {
    printf("%-38s %d\n", $t, $c);
} ?>
