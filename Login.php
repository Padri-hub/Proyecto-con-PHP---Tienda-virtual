<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Toledo Petals</title>
    <link type="text/css" rel="stylesheet" href="CSS/estilosLogin.css">
    <link rel="stylesheet" href="CSS/estilosIndex.css" type="text/css">
</head>
<body>
    <input type="radio" name="form" id="login" checked>
    <input type="radio" name="form" id="registro">

    <div id="C-principal">
        <div id="C-2">
            <div id="C-opciones">
                <label for="login" class="login">Login</label>
                <label for="registro" class="registro">Registro</label>
            </div>
            <div id="formularios">
                <form method="post" id="F-login" action="<?php echo $_SERVER["PHP_SELF"] ?>">   
                    <div class="errores">
                    <?php   //Contenedor para mostrar los diferentes errores que puede tener el usuario
                        if(isset($_GET["error"])) echo "Debes iniciar sesión para acceder a la página";
                        if(isset($_GET["error-correo"])) echo "Usuario no encontrado";
                        if(isset($_GET["error-clave"])) echo "Contraseña incorrecta";
                    ?> 
                    </div>
                        
                    <input type="text" name="correo" value="<?php if(isset($_GET["correo"])) echo $_GET["correo"] ?>" placeholder="Correo"><br>
                    <input type="password" name="clave" placeholder="Clave"><br>
                    <button type="submit" class="botones" name="acceder">Acceder</button>
                </form>
                <form method="post" id="F-registro" action="<?php echo $_SERVER["PHP_SELF"] ?>">
                    <div class="errores">
                    <?php   //Contenedor para mostrar los diferentes errores que puede tener el usuario
                        if(isset($_GET["error-correo-r"])) echo "Usuario ya registrado";
                        if(isset($_GET["error-clave-r"])) echo "Las contraseñas no coinciden";
                    ?>
                    </div>
                    <input type="text" value="<?php if(isset($_GET["correo-r"])) echo $_GET["correo-r"] ?>" name="correo-r" placeholder="Correo" required><br>
                    <input type="text" value="<?php if(isset($_GET["nombre"])) echo $_GET["nombre"] ?>" name="nombre" placeholder="Nombre" required><br>
                    <input type="text" value="<?php if(isset($_GET["calle"])) echo $_GET["calle"] ?>" name="calle" placeholder="Calle" required><br>
                    <input type="text" value="<?php if(isset($_GET["localidad"])) echo $_GET["localidad"] ?>" name="localidad" placeholder="Localidad" required><br>
                    <input type="text" value="<?php if(isset($_GET["cp"])) echo $_GET["cp"] ?>" name="cp" placeholder="CP" required><br><br>

                    <input type="password" name="clave" placeholder="Clave" required><br>
                    <input type="password" name="clave-confirma" placeholder="Confirmar la clave" required><br>
                    <button type="submit" class="botones" name="registrar">Registrarse</button>
                </form>
            </div>
        </div>
    </div>

    <header>
    </header>

    <footer>
        <div class="container">
            <p class="text-center">&copy; Jardinería 2023 - Todos los derechos reservados</p>
        </div>

    </footer>
    
</body>
</html>

<?php

    if ($_SERVER["REQUEST_METHOD"] == "POST"){

        /*-------------------Boton de acceder------------------------*/
        if(isset($_POST["acceder"])){
            include ("conexion.php");//conexion bd sql
    
            $correo = $_POST["correo"];
            //hago la consulta
            $consulta = $db->query("SELECT correo, clave, nombre, saldo FROM usuarios WHERE Correo = '$correo';");
            //si el usuario coincide sigo con el código
            if ($consulta->rowCount() == 1){
                session_start();

                $usuario = $consulta->fetch(PDO::FETCH_ASSOC);//asigno a una variable los datos del usuario

                if($usuario["clave"] == $_POST["clave"]){//y compruebo la contraseña
                    $_SESSION["correo"] = $usuario["correo"];
                    $_SESSION["nombre"] = $usuario["nombre"];
                    $_SESSION["saldo"] = $usuario["saldo"];
                    header("Location: index.php");
                }
                else{
                    header("Location: login.php?error-clave=true&correo=$correo");
                    exit();
                }
            }
            else{
                //si no encuentra al usuario redirige al login con un error
                header("Location: login.php?error-correo=true");
                exit();
            }
    
        }

        /*--------------------------Boton de registrarse----------------------*/

        if(isset($_POST["registrar"])){
            include ("conexion.php");//conexion bd sql
    
            $correo = $_POST["correo-r"];
            $nombre = $_POST["nombre"];
            $calle = $_POST["calle"];
            $localidad = $_POST["localidad"];
            $cp = $_POST["cp"];
            $clave = $_POST["clave"];

            //hago la consulta
            $consulta = $db->query("SELECT correo FROM usuarios WHERE Correo = '$correo';");
            //compruebo si ese correo ya está registrado
            if ($consulta->rowCount() == 0){

                if ($_POST["clave"] == $_POST["clave-confirma"]){//ahora compruebo si las claves son iguales
                    //si todo esta bien inicio una sesion e introduzco el usuario 
                    session_start();
                    $saldo = 50;
                    $insercion = $db->query("INSERT INTO usuarios(correo, clave, nombre, calle, localidad, cp, saldo) VALUES('$correo', '$clave', '$nombre', '$calle', '$localidad', '$cp', $saldo);");

                    //compruebo que todo haya saldo bien antes de redirigir al usuario 
                    if($insercion){
                        $_SESSION["correo"] = $correo;
                        $_SESSION["nombre"] = $nombre;
                        $_SESSION["saldo"] = $saldo;
                        header("Location: index.php?registrado=true");
                    }
                    else print_r($db->errorinfo());

                }
                else{
                    header("Location: login.php?error-clave-r=true&correo-r=$correo&nombre=$nombre&calle=$calle&localidad=$localidad&cp=$cp");
                    exit;
                }
            }
            else{
                //si el usuario ya está registrado le redirijo al login con un mensaje de error
                header("Location: login.php?error-correo-r=true");
                exit();
            }
    
        }
    }


?>