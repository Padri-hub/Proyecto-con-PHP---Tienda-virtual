<?php
    session_start();

    $_SESSION = array();

    /*Elimino la coockie: */
    setcookie(session_name(), 123, time() - 1000);
    /*Por ultimo me cargo la sesion*/
    session_destroy();
    /*Luego redirijo al login */
    header("Location: Login.php");
?>