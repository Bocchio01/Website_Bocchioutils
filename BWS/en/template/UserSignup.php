<html>

<head>

    <style>
        h1 {
            text-align: center;
            font-size: 25px;
            margin-bottom: 20px;
        }

        p {
            font-size: 15px;
        }
    </style>

</head>

<body>
    <h1>Welcome <?php echo $nickname ?>!</h1>
    <p>
        Thank you for subscribing to my site and so supporting me in what I do :).<br>
        With this message I simply want to check that the e-mail you entered in the site form is correct.<br><br>
        Please click on <a href='https://bocchioutils.altervista.org/BWS/en/?action=VerifyEmail&tmp=<?php echo $tmp ?>'>this link</a> to confirm it.<br><br>
        If you think this e-mail was sent to the wrong receiver, you can ignore it.<br><br>
        The WebMaster of Bocchio's WebSite,<br>
        Tommaso<br>
    </p>
</body>

</html>