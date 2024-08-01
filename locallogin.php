<?php

require_once "function.php";
require_once "auth.php";
if ($ur && $afm)
{
    redirect("index.php");
    die;

}

require_once "output.php";

if (array_key_exists("password2",$_POST))
{
    $e = Single("USERS","AFM",(int)$req['username']);
    if ($e)
        {
            echo 'Ο λογαριασμός υπάρχει ήδη!';
            die;
        }
    if ($req['password'] != $req['password2'])
    {
        echo 'Οι κωδικοί δεν είναι ίδιοι!';
        die;
    }

    QQ("INSERT INTO USERS (MAIL,AFM,LASTNAME,FIRSTNAME,CLSID,PASSWORD) VALUES(?,?,?,?,?,?)",array(
        $req['email'],$req['username'],$req['lastname'],$req['firstname'],guidv4(),password_hash($req['password'],PASSWORD_DEFAULT)
    ));
    echo 'Ο λογαριασμός δημιουργήθηκε! Κάντε <a href="locallogin.php">Login</a>.';
    die;
}

if (array_key_exists("password",$_POST))
{
    $f = 1;
    $e = Single("USERS","AFM",$req['username']);
    if ($e)
    {
        if (password_verify($req['password'],$e['PASSWORD']))
            $f = 0;
    }
    if ($f)
        die("Λάθος username/password!");
    else
    {
        $_SESSION['afm2'] = $req['username'];
        redirect("index.php");
    }
    die;
}

if (array_key_exists("register",$_GET))
{
    ?>
<div class="content" style="margin: 20px;">
Register
<hr>
<form method="POST" action="locallogin.php">
    ΑΦΜ:
    <input type="text" name="username" required class="input" autofocus/><br><br>
    Επίθετο:
    <input type="text" name="lastname" required class="input"/><br><br>
    Όνομα:
    <input type="text" name="firstname" required class="input"/><br><br>
    e-mail:
    <input type="email" name="email" required class="input"/><br><br>
    Password:
    <input type="password" name="password" required class="input"/><br><br>
    Password ξανά:
    <input type="password" name="password2" required class="input"/><br><br>
    <button class="button is-success">Register</button>
</form>
<button class="button is-danger autobutton" href="index.php">Πίσω</button><br><br>

    <?php
    die;
}

?>
<div class="content" style="margin: 20px;">
Login
<hr>
<form method="POST" action="locallogin.php">
    ΑΦΜ:
    <input type="text" name="username" required class="input" autofocus/><br><br>
    Password:
    <input type="password" name="password" required class="input"/><br><br>
    <button class="button is-success">Login</button>
</form>
<button class="button is-danger autobutton" href="index.php">Πίσω</button><br><br>

Αν δεν έχετε τοπικό login, κάντε <a href="locallogin.php?register=1">Register</a> τώρα.

</div>