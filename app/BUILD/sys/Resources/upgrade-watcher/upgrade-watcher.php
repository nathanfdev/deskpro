<?php
/** @var string $ASSET_URL */
/** @var string $ASSET_PATH */
/** @var string $BASE_URL */
/** @var string $BASE_PATH */
?>
<!DOCTYPE html>
<html ng-app id="upgrader">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width">
    <title>DeskPRO</title>

    <link rel="stylesheet" href="<?=$ASSET_PATH?>/app-build/Admin/Resources/style/admin-style.css"/>
    <link rel="stylesheet" href="<?=$ASSET_PATH?>/app-build/Admin/Resources/style/admin2-style.css"/>
    <style>
        .footer-alert {
            display: none;
        }

        .done-load .footer-alert {
            display: block;
        }
    </style>
</head>
<body>

<table class="layout_table" width="100%" height="100%" cellspacing="0" cellpadding="0">
    <tr>
        <td width="100%" height="100%" align="center" valign="center">
            <div class="main_content" ng-controller="AdminUpdateWatcher_Ctrl_Main">
                <?php require __DIR__.'/upgrade-watcher-content.php'?>
            </div>
        </td>
    </tr>
</table>

<script type="text/javascript">
    window.DP_ASSET_URL       = '<?=$ASSET_URL?>';
    window.DP_BASE_URL        = '<?=$BASE_URL?>';
    window.DP_USE_RJS_BUILD   = false;
    window.DP_BUILD_TIME      = (new Date()).getTime();
    window.DP_SERVERINFO_AUTH = '<?=$DP_AUTH?>';
    window.DP_IS_DEBUG        = false;
</script>

<script type="text/javascript">
    window.name = "NG_DEFER_BOOTSTRAP!";
    window.DP_INTERFACE_LOADER = 'AdminUpdateWatcherLoad';
</script>
<script	type="text/javascript" data-main="<?=$ASSET_PATH?>/loader-build/requirejs-config.js?v=<?=time()?>" src="<?=$ASSET_PATH?>/bower_components/requirejs/require.js"></script>
</body>
</html>
