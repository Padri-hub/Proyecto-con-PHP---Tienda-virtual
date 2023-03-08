<?php

/*creo un objeto articulo donde se van a guardar cada dato de la compra realizada por el cliente */
    include("bd.php");

    session_start();

    if(!isset($_SESSION["correo"])) header("Location: Login.php?error=true");//comprobacion de que el usuario está autenticado
 
    /*Si recibe el nombre de un articulo lo añade al carrito */
    if(isset($_POST["nombre-articulo"])) insertaArticulo();

    /*metodo que uso a la hora de modificar la cantidad de un objeto*/
    if(isset($_POST["cambiaValor"])) modificaArticulo();


    if(isset($_POST["comprar"])) comprarProductos();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="CSS/estilosCarrito.css" type="text/css">
    <title>Carrito</title>
</head>
<body>
    <nav>
        <a href="index.php" class="titulo2">Floristeria Petals</a>
        
        <p class="titulo2">
            <span class="botonInicio" ><a href="index.php" ><img src="https://cdn-icons-png.flaticon.com/512/9138/9138521.png" alt="volver"></a></span>
            <span class="saldo">Saldo: <?php echo $_SESSION["saldo"] ?>€</span> | Carrito 

        </p>
    </nav>

    <article>

        <?php
        //metodo que uso para mostrar la lista de objetos en el carrito
        //primero miro si existe el carrito y luego si tiene objetos
            $cantidad_total = 0;    //cantidad total del dinero que cuestan los productos que voy incrementando segun muestro los productos en la página
            
            if (isset($_SESSION["carrito"]) && count($_SESSION["carrito"]) > 0){
                
                //genero una tabla, que es como voy a mostrar los productos al usuario
                echo "
                <table cellspacing='0' class='tablaobjetos'>
                <tr>
                    <th>Imagen</th>
                    <th>Nombre</th>
                    <th>Descripcion</th>
                    <th>Precio</th>
                    <th>Cantidad</th>
                </tr>
                ";
                
                foreach($_SESSION["carrito"] as $objeto){
                    //ahora genero cada objeto dentro de la tabla
                    echo "
                    <tr>
                        <td><img src='$objeto->imagen' alt='flores'></td>
                        <td>$objeto->nombre</td>
                        <td>$objeto->descripcion</td>
                        <td>$objeto->precio</td>
                        <td>
                            <form action='". $_SERVER['PHP_SELF']. "?producto=$objeto->id" . "' method='POST'>
                            <div>
                                <button type='submit' name='restaCantidad' class='restaCantidad botones'>-</button>
                                <p>$objeto->cantidad</p>
                                <input type='hidden' name='cambiaValor'>
                                <input type='hidden' name=''>
                                <button type='submit' name='sumaCantidad' class='sumaCantidad botones'>+</button><br>
                            </div>
                                <button type='submit' name='borrarProducto' class='btnEliminar botones'><img src='https://cdn.icon-icons.com/icons2/1959/PNG/256/12_122766.png' alt='cubo' class='imgEliminar'></button>
                            </form>
                        </td>
                    </tr>
                    ";
                    //tambien almaceno la cantidad total para mostrarla al usuario
                    $cantidad_total += $objeto->cantidad * $objeto->precio;
                }
                echo "</table>";
            }
            else echo "<table class='tablaobjetos'><tr><td>Sin articulos</td></tr></table>";

        ?>
        


        <table class="tablaprecio" cellspacing="0">
            <?php
            if (isset($_SESSION["carrito"]) && $cantidad_total > 0){
                echo "
                <tr>
                    <th>Resumen Compra</th>
                </tr>
                <tr>
                    <td>
                        <h4>Precio Total</h4>
                        <h4><span style='color:white;'>$cantidad_total €<span> IVA inc.</h4>
                    </td>
                </tr>

                <tr>
                    <td>
                        <form action='carrito.php' method='POST'>
                        <button type='submit' name='comprar' style='background-color: gold;'  class='comprar'>Comprar</button>
                        </form>                        
                    </td>
                </tr>";
                if (isset($_GET["error_saldo"])) echo "<tr><td> Saldo insuficiente </td></tr>";
            }


            ?>
        </table>
    
    </article>

    <footer>
        <div class="container">
            <p class="text-center">&copy; Jardinería 2023 - Todos los derechos reservados</p>
        </div>
    </footer>
</body>
</html>
