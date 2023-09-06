<?php

use Slim\Routing\RouteCollectorProxy;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

$app->group('/BWS/user', function (RouteCollectorProxy $group) {

    $group->post('/signup', function (Request $request, Response $response, $args) {
        $RCV = (object) $request->getParsedBody();

        $nickname = $RCV->nickname;
        $email = strtolower($RCV->email);
        $password = $RCV->password;
        $lang = $RCV->preferences->lang;

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) die(returndata(1, "Email format not valid."));
        if (strlen($password) < 5) die(returndata(1, "Password must have at least 5 characters."));

        $password = md5($password);

        $token = CreateToken();
        $tmp = CreateToken(5);

        Query("INSERT INTO BWS_Users (nickname, email, password, lang, token, tmp) VALUES ('$nickname','$email','$password','$lang','$token','$tmp')");

        $message = render('./template/' . $lang . '/UserSignup.php', array('nickname' => $nickname, 'tmp' => $tmp));

        // if (mail($email, $subject, $message, $headers)) $return_obj->Log[] = "An e-mail has just been sended to: $email";
        // else die(returndata(1, "There was a problem while sending e-mail to: $email\r\nCheck the e-mail or try again later."));
    });


    $group->post('/login', function (Request $request, Response $response, $args) {
        $RCV = (object) $request->getParsedBody();

        $token = false;
        if (!empty($_COOKIE['token'])) $token = $_COOKIE['token'];
        $email = strtolower($RCV->email);
        $password = md5($RCV->password);
        $autologin = $RCV->autologin;

        if ($password && $email) $result = Query("SELECT * FROM BWS_Users WHERE email = '$email'");
        else $result = Query("SELECT * FROM BWS_Users WHERE token = '$token'");

        if ($result->num_rows) {
            $row = $result->fetch_array(MYSQLI_ASSOC);

            if ($row['password'] == $password || $token) {

                if ($row['verified'] == 1) {
                    $payload = [
                        'id' => $row['id_user'],
                        'nickname' => $row['nickname'],
                        'email' => $row['email'],
                        'password' => $RCV->password,
                        'autologin' => $autologin,
                        'preferences' => [
                            'theme' => $row['theme'],
                            'color' => $row['color'],
                            'font' => (int) $row['font'],
                            'avatar' => $row['avatar'],
                            'lang' => $row['lang'],
                            'newsletter' => (bool) $row['newsletter'],
                        ]
                    ];

                    Query("UPDATE BWS_Users SET last_login=NOW() WHERE id_user = '$row[id_user]'");

                    if ($token || $autologin) Cookie($row['token']);
                    else setcookie('token', "$row[token]", [
                        'expires' => 0,
                        'path' => '/',
                        'samesite' => 'None',
                        'secure' => 'Secure',
                        'httponly' => false,
                    ]);
                } else {
                    ClearCookie();
                    $payload = [
                        'status' => 1,
                        'message' => "You mut verify your e-mail before logging in. Check your e-mail box."
                    ];
                }
            } else {
                ClearCookie();
                $payload = [
                    'status' => 1,
                    'message' => "The password is uncorrect!"
                ];
            }
        } else {
            ClearCookie();
            $payload = [
                'status' => 1,
                'message' => "The user doesn't exist. Check the e-mail you entered."
            ];
        }

        $response->getBody()->write(json_encode($payload));
        return $response
            ->withHeader('Content-Type', 'application/json');
    });

    $group->get('/logout', function (Request $request, Response $response, $args) {
        ClearCookie();
        return $response;
    });

    $group->put('/update', function (Request $request, Response $response, $args) {
        $RCV = (object) $request->getParsedBody();

        $id_user = $RCV->id;
        $nickname = $RCV->nickname;
        $theme = $RCV->preferences->theme;
        $color = $RCV->preferences->color;
        $font = (int) $RCV->preferences->font;
        $avatar = $RCV->preferences->avatar;
        $lang = $RCV->preferences->lang;
        $newsletter = (int) $RCV->preferences->newsletter;

        Query("UPDATE BWS_Users SET theme='$theme', color='$color', font=$font, avatar='$avatar', lang='$lang', newsletter=$newsletter WHERE id_user='$id_user'");
        if (strlen($nickname) > 0) {
            Query("UPDATE BWS_Users SET nickname='$nickname' WHERE id_user='$id_user'");
            $payload = [
                'status' => 0,
                'message' => "User updated successfully."
            ];
        } else
            $payload = [
                'status' => 1,
                'message' => "Nickname can't be null."
            ];

        $response->getBody()->write(json_encode($payload));
        return $response
            ->withHeader('Content-Type', 'application/json');
    });
});
