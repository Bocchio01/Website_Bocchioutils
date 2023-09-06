<?php

require_once __DIR__ . "/DB.php";

class UserService
{
    protected $db;

    public function __construct()
    {
        $this->db = (new DB())->connect();
    }

    public function login(string $email, string  $password, bool $autologin, string $token = null)
    {
        $token = false;
        if (!empty($_COOKIE['token'])) $token = $_COOKIE['token'];

        if ($password && $email) {
            $query = "SELECT * FROM BWS_Users WHERE email = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$email]);
        } else {
            $query = "SELECT * FROM BWS_Users WHERE token = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$token]);
        }

        if ($stmt->rowCount()) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row['password'] == $password || $token) {

                if ($row['verified'] == 1) {
                    return [
                        'id' => $row['id_user'],
                        'nickname' => $row['nickname'],
                        'email' => $row['email'],
                        'password' => $password,
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
                } else {
                    return 'User not verified.';
                }
            } else {
                return 'Password not valid.';
            }
        } else {
            return 'User not found.';
        }
    }

    public function signup($nickname, $email, $password, $lang)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return 'Email format not valid.';
        }

        if (strlen($password) < 5) {
            return 'Password must have at least 5 characters.';
        }

        $password = md5($password);

        // Generate tokens
        $token = CreateToken();
        $tmp = CreateToken(5);

        // Insert user data into the database
        $query = "INSERT INTO BWS_Users (nickname, email, password, lang, token, tmp) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$nickname, $email, $password, $lang, $token, $tmp]);

        // // Send email
        // $subject = 'User Registration';
        // $message = render($this->templatePath . '/' . $lang . '/UserSignup.php', ['nickname' => $nickname, 'tmp' => $tmp]);

        // if (mail($email, $subject, $message)) {
        //     return "An email has been sent to: $email";
        // } else {
        //     return "There was a problem while sending an email to: $email. Please check the email address or try again later.";
        // }
    }
}
