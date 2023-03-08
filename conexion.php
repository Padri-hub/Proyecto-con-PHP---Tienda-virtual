<?php
//decimos donde nos vamos a conectar, mysql, con el nombre de la base de datos empresa.
//decimos que nos conectamos al host, localhost o en la ip 127.0.0.1 que es lo mismo.
$cadena_conexion = "mysql:dbname=Floristeria Petals;host=127.0.0.1";
$usuariobd = "root"; //con el usuario root
$clavebd = ""; //y su contraseña que es ninguna 

$db = new PDO($cadena_conexion, $usuariobd, $clavebd);//creamos la conexion a la base da datos 

?>
