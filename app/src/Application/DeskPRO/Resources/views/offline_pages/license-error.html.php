<?php include __DIR__ . '/tpl-header.html.php' ?>
    <div style="background-color: #ededed; padding: 8px; border-radius: 6px; -webkit-border-radius: 6px;">
        <strong>Renew DeskPRO</strong><br />
        If you would like to continue using DeskPRO, go to the DeskPRO members area to renew your license.<br/>
        <a class="btn btn-primary" href="https://www.deskpro.com/members/">DeskPRO Members Area &rarr;</a>
    </div>

    <div style="margin-top: 10px; background-color: #ededed; padding: 8px; border-radius: 6px; -webkit-border-radius: 6px;">
        <strong>Already have a new license code?</strong><br />
        If you already have a new DeskPRO license code, you need to go to the Billing Interface to activate it.<br/>
        <a class="btn btn-info" href="<?php echo $billing_url; ?>">Input your license code &rarr;</a>
    </div>
<?php include __DIR__ . '/tpl-footer.html.php' ?>
