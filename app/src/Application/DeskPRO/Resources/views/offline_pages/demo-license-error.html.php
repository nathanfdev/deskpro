<?php include __DIR__ . '/tpl-header.html.php' ?>
    <div style="background-color: #ededed; padding: 8px; border-radius: 6px; -webkit-border-radius: 6px;">
        <strong>Purchase DeskPRO</strong><br />
        If you would like to continue using DeskPRO, go to our website to purchase a license.<br/>
        <a href="https://www.deskpro.com/buy/" class="btn btn-primary">Purcahse DeskPRO &rarr;</a>
    </div>

    <div style="margin-top: 10px; background-color: #ededed; padding: 8px; border-radius: 6px; -webkit-border-radius: 6px;">
        <strong>Already have a license?</strong><br />
        If you already have a DeskPRO license, you need to go to the Billing Interface to activate it.<br/>
        <a class="btn btn-info" href="<?php echo $billing_url; ?>">Input your license code &rarr;</a>
    </div>

    <?php include __DIR__ . '/tpl-contact.html.php' ?>
<?php include __DIR__ . '/tpl-footer.html.php' ?>
