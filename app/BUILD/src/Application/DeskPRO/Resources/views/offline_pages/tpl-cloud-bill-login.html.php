<?php if ($is_widget): ?>
    <br/><br/>
    <a href="<?php echo $billing_url ?>" class="btn">Go to the billing interface &rarr;</a>
<?php else: ?>
    <form action="<?php echo $billing_url ?>login/authenticate-password" method="POST">
        <div style="margin-top: 45px;  background-color: #FFF; border: 1px solid #E8E8E8; padding: 8px; border-radius: 6px; -webkit-border-radius: 6px;">
            <strong style="font-size: 13pt; margin-botom: 6px;">Log in now to add your billing information.</strong><br/><br/>
            <a href="<?php echo $billing_url ?>" class="btn btn-primary">Go to the billing interface &rarr;</a>
        </div>
    </form>
<?php endif ?>
