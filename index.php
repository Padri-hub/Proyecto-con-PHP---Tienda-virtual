<?php
    session_start();
    if(!isset($_SESSION["correo"])) header("Location: Login.php?error=true");//comprobacion de que el usuario está autenticado

    include("bd.php"); //conexion bd sql
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Toledo Petals</title>
    <link rel="stylesheet" href="CSS/estilosIndex.css" type="text/css">
</head>

<body>

    <div class="items">
        <?php
        /*Si hay productos en el carrito muestro la cantidad */
            if(isset($_SESSION["carrito"]) && count($_SESSION["carrito"]) > 0) echo count($_SESSION["carrito"]);
        ?>
    </div>

    <div class="emergente">
        <?php
        if(isset($_GET["compra"])) echo '<span>Producto añadido al carrito </span>
                                        <img class="icon"  src="https://cdn.icon-icons.com/icons2/317/PNG/512/sign-check-icon_34365.png">'
        ?>
    </div>

    <script type="text/javascript">
            function categorias() {
                categoria = document.getElementById("categorias").value;
                location.href = "index.php?categoria=" + categoria;
            }

            let emergente = document.getElementsByClassName("emergente")[0];
            emergente.style.opacity = 1;
            setTimeout(function() {
                emergente.style.opacity = 0;
            }, 2000) // segundos que dura la ventana emergente
            
        </script>

    <nav>
        <select name="Categoria" id="categorias" class="form" onchange="categorias()">
            <?php
                SeleccionarCategoria();//añade cada categoria al selector
            ?>
        </select>
        <!--funcion que uso para pasarle la categoria de js a php por la URL-->

        <a href="index.php" class="titulo2">Floristeria Petals</a>
        <div>
            <a href="carrito.php" class="btnCarrito"><img src="https://cdn.pixabay.com/photo/2017/06/07/18/35/design-2381160_960_720.png" height="30" width="30"/></a>
            
            <h3 class="saldo">Saldo: <?php echo $_SESSION["saldo"] ?>€</h3>
            <a href="logout.php" class="btnLogOut botones"><img src="https://cdn-icons-png.flaticon.com/512/5509/5509597.png" height="30" width="30" /></a>
        </div>
    </nav>

    <header>
        <div>
        <p class="titulo">Floristeria Petals</p>
        <h3>Bienvenido a nuestra tienda, ofrecemos una amplia variedad de plantas, herramientas y productos de jardinería,
            además de asesoramiento de expertos. Transforma tu espacio exterior en un oasis de tranquilidad y belleza con nuestra ayuda.
            ¡Registrate ahora!
        </h3>
        </div>
    </header>

    <article>
        <?php
        /*Metodo para obtener los productos de una categoria */
        if (isset($_GET["categoria"])) Mostarproducto($_GET["categoria"]);
        ?>

    </article>

    <footer>
        <div class="container">
            <p class="text-center">&copy; Jardinería 2023 - Todos los derechos reservados</p>
        </div>

    </footer>

    <div id="myModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span><br><br>
            <h3>Registro Completo, aqui tienes tu regalo</h3>
            <h3>50€</h3>
            <img src="https://cdn-icons-png.flaticon.com/512/7020/7020787.png" alt="iconoDinero" height="100px" width="100px">
            <h5>Úsalo en tu próxima compra</h5>
        </div>
    </div>

    <script>
        function saldoInicial(){
                var modal = document.getElementById("myModal");
                modal.style.display = "block";
                 // Cerrar el modal al hacer clic en la X;
                var span = document.getElementsByClassName("close")[0];
                span.onclick = function() {
                    modal.style.display = "none";
                };
        }

    </script>

    <?php
    /*Aqui miro si el la primera vez que entra el usuario a la página web*/
    if(isset($_GET["registrado"])) echo "<script> saldoInicial(); </script>";
    ?>

</body>

</html>