<?php if (!defined('DP_ROOT')) {
    exit('No access');
} ?>
<?php $view->extend('InstallBundle:Install:layout.html.php') ?>
<?php $view['slots']->start('subtitle') ?>Step 5: Installing database<?php $view['slots']->stop() ?>
<style>
    .condensed-table th, .condensed-table td {
        border: none;
    }
</style>
<script type="text/javascript">
var installer = {
    begin: function() {
        var initialLine = $('<tr><td>Running installer... This will take a few minutes. &nbsp; <img src="../../web/images/spinners/loading-small-flat.gif" alt="Installng now..." /></td></tr>');
        $('#logTable').append(initialLine);

        $.ajax({
            url: "<?php echo $view['router']->generate('install_create_tables_do') ?>",
            dataType: "json",
            cache: false,
            timeout: 900000,
            success: function(data) {
                installer.addLogLine(data.output);
                if (data.is_success) {
                    installer.doneSuccess();
                } else {
                    installer.doneFailure();
                }
            },
            error: function(xhr, textStatus, errorThrown) {
                installer.addLogLine("Installer failed with status: " + (textStatus || 'other'));
                installer.doneFailure();
            },
            complete: function() {
                $('#spinner').hide();
                initialLine.find('img').remove();
                $('body').addClass('is-finished-proc');
            }
        });
    },

    addLogLine: function(str) {
        var line = $('<tr><td></td></tr>');
        line.find('td').html(str.replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/\n/g, '<br/>'));
        $('#logTable').append(line);
        $('#install_log').val($('#install_log').val() + str);
    },

    doneSuccess: function() {
        installer.addLogLine("Install process is done with SUCCESS status.");
        $('#install_done').show();
    },

    doneFailure: function() {
        installer.addLogLine("Install process is done with ERROR status.");
        $('#install_error').show();
        $('#show_log').click();
    }
};

$(document).ready(function () {
    $('#show_log').on('click', function () {
        $(this).hide();
        $('#hide_log').show();
        $('#spinner').hide();
        $('#log').show();
    });
    $('#hide_log').on('click', function () {
        $(this).hide();
        $('#show_log').show();
        if (!$('body').hasClass('is-finished-proc')) {
            $('#spinner').show();
        }
        $('#log').hide();
    });
    installer.begin();
});
</script>
<style type="text/css">

    #install_loading .progress {
        border: 2px solid #62CFFC;
        border-radius: 8px;
        -moz-border-radius: 8px;
    }

    #install_loading table {
        margin: 0;
        padding: 0;
        border: none;
    }

    #install_loading table td {
        padding: 0;
        margin: 0;
        background-color: #fff;
        border: none;
        height: 20px;
        overflow: hidden;
        border-radius: 6px;
        -moz-border-radius: 6px;
    }

    #install_loading table td.done {
        background-color: #A3D4FB;
    }

    #install_error textarea {
        width: 80%;
        height: 120px;
        overflow: auto;
    }

    #error_list {
        max-height: 200px;
        overflow: auto;
    }
</style>

<div id="install_loading">
    <div id="show_log" style="float: right; cursor: pointer"><span class="label notice">Show Log</span></div>
    <div id="hide_log" style="float: right; cursor: pointer; display: none"><span class="label notice">Hide Log</span></div>

    <h3>Installing Database</h3>
    <div class="well" style="margin-top: 8px; text-align: center;" id="spinner">
        <img src="../../web/images/spinners/loading-big-circle.gif" alt="Installng now..." />
    </div>
</div>

<div id="log" class="well" style="display: none; margin-top: 8px; max-height: 400px; overflow: auto;">
    <table class="condensed-table" id="logTable">
        <tbody>
        </tbody>
    </table>
</div>

<br />

<div id="install_error" style="display: none">
    <div class="alert-message block-message error">
        <strong>There was an error!</strong> An error was detected during the installation.

        <p>You should contact DeskPRO Support to get help on how to fix this error. Include the above log with any message you send to us.</p>

        <div class="alert-actions">
            <a class="btn" href="mailto:support@deskpro.com">Email support@deskpro.com</a>
            <a class="btn" href="http://support.deskpro.com/">Visit our helpdesk</a>
        </div>
    </div>
</div>

<div id="install_done" style="display: none">
    <div class="alert-message block-message success">
        <strong>Done!</strong> You're ready to go to the next step.

        <div class="alert-actions submit-area">
            <a class="btn" tabindex="2" id="next_btn" href="<?php echo $view['router']->generate('install_install_done') ?>" onclick="if (!$(this).hasClass('disabled')) { $(this).parent().addClass('clicked'); }">Continue</a>
            <span class="next-loading"></span>
        </div>
    </div>
</div>
