<html>

<head>

    <style>
        <?php echo $style ?>
    </style>

</head>

<body>
    <h1>Recovery/change of credentials!</h1>
    <p>
        Hi, if you have received this e-mail it means that someone has requested a "password recovery" for the account created on "Bocchio's WebSite" linked to this e-mail.<br>
        If you haven't requested this service, you should contact <a href="mailto:tommaso.bocchietti@gmail.com?subject=Unexpected site credentials recovery&body=Request made for e-mail account: <?php echo $email ?>">the site administration.</a><br><br>
        Otherwise, click on <a href='<?php echo UTILS_SITE ?>/BWS/site/?l=en&action=ModifyPasswordEmail&email=<?php echo $email ?>'>this link</a> and you will be able to generate a new password.<br><br>
        In the field "Temporary password" of the form, enter: <?php echo $tmp ?><br><br>
        The WebMaster of Bocchio's WebSite,<br>
        Tommaso<br>
    </p>
</body>

</html>