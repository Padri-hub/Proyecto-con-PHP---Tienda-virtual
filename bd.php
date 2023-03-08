<?php
include ("conexion.php");//conexion bd sql

function Mostarproducto($categoria){
    
    //consulta
    $sql = "SELECT Id_Producto, Nombre, Descripcion, Imagen, Precio, Stock  FROM productos WHERE Categoria='$categoria'"; 
    global $db;
    $re = $db->query($sql); 

    //guardo la url para mandarla a la página al guardar el articulo
    $url = $_SERVER["REQUEST_URI"];
    
    //recorro todos los articulos que me devuelve la consulta y creo una caja con el objeto y diferentes opciones
    foreach ($re as $row) {
        echo '<div class="cuadrado">';
        echo '<p> '.$row["Nombre"]. "</p>";
        echo '<img class="imagenes" src="'.$row["Imagen"].'">';
        echo '<p> Precio '.$row["Precio"]. "</p>";
        echo '<form action="carrito.php" method="post">
        <input type="hidden" name="id-articulo" value="'. $row["Id_Producto"] . '">
        <input type="hidden" name="nombre-articulo" value="'.$row["Nombre"].'">
        <input type="hidden" name="imagen-articulo" value="'.$row["Imagen"].'">
        <input type="hidden" name="precio-articulo" value="'.$row["Precio"].'">
        <input type="hidden" name="descripcion-articulo" value="'.$row["Descripcion"].'">
        <input type="hidden" name="stock-articulo" value="'.$row["Stock"].'">
        <input type="hidden" name="URL" value="'.$url.'">
        <select name="cantidad-articulo">
            <option value="1">1</option>
            <option value="2">2</option>
            <option value="3">3</option>
            <option value="4">4</option>
            <option value="5">5</option>
        </select>
        <input class="comprar" type="submit" value="Comprar">
            </form>';
        echo "</div>";
    }
   
}

function SeleccionarCategoria(){
    // Consulta para obtener las categorías de productos
    $query = "SELECT DISTINCT Categoria FROM productos";
    global $db;
    $result = $db->query($query); 
    echo '<option value="Seleccione una categoria">Seleccione una categoria</option>';
    // Iterar a través de los resultados de la consulta y agregarlos como opciones en el select
    foreach ($result as $row) {
        echo '<option value="'.$row['Categoria'].'">'.$row['Categoria'].'</option>';
    }
}

function insertaArticulo(){
    //variable que utilizo para inicializar o no un nuevo objeto
    $nuevo = TRUE;
        
    //recojo los datos del articulo que he comprado
    $id = $_POST["id-articulo"];
    $nombre = $_POST["nombre-articulo"];
    $descripcion = $_POST["descripcion-articulo"];
    $imagen = $_POST["imagen-articulo"];
    $precio = $_POST["precio-articulo"];
    $cantidad = $_POST["cantidad-articulo"];
    $stock = $_POST["stock-articulo"];
    $url = $_POST["URL"];

    //aqui miro si ese objeto ya está dentro del carrito
    if (isset($_SESSION["carrito"])){
        foreach($_SESSION["carrito"] as $objeto){
            if ($id == $objeto->id){//en caso de que si le sumo a la cantidad lo que haya introducido el usuario
                if (miraStock($objeto, $cantidad)) $objeto->cantidad += $cantidad;  //antes de modificar la cantidad de objetos que quiere el usuario compruebo que no excedan el limite de stock
                $nuevo = FALSE;
            }
        }
    }

    //y sino está en el array lo inicializo con esos datos
    if ($nuevo) $_SESSION["carrito"][] = new articulo($id, $nombre, $descripcion, $imagen, $precio, $cantidad, $stock);
    
    $url = explode("&", $url)[0]; 
    header("Location: $url&compra=true");
}

function modificaArticulo(){
    //itero el carrito
    foreach ($_SESSION["carrito"] as $indice => $objeto){
        //y miro el objeto que ha pulsado un boton con una variable GET
        if ($_GET["producto"] == $objeto->id){
            //si existe la variable post con nombre de objeto es que el usuario ha clicado sobre ese objeto y quiere modificar la cantidad, ya sea a mas, menos o eliminarlo directamente
            //salgo del bucle para guardar el objeto
            break;
        }
    }
    //depende de que boton pulse hago una accion
    if (isset($_POST["restaCantidad"])) $objeto->cantidad --;
    elseif (isset($_POST["sumaCantidad"])) if(miraStock($objeto, 1)) $objeto->cantidad ++;
    
    //aqui compruebo si el usuario ha pulsado el boton de eliminar objeto o la cantidad es 0 para eliminarlo del array
    if (isset($_POST["borrarProducto"]) || $objeto->cantidad == 0){
        unset($_SESSION["carrito"][$indice]);
    }

    header("Location: carrito.php");
}


//metodo sencillo para mirar si puedo introducir mas articulos
function miraStock(articulo $articulo, $cantidad){
    if ($articulo->stock < ($articulo->cantidad + $cantidad)) return false;
    else return true;
}



/*metodo para realizar la compra de los articulos en la cesta*/
function comprarProductos(){
    global $db;

    $db->beginTransaction();

    $operaciones = TRUE;//variable que utilizo para comprobar las consultas
    $precio_final = 0;
    $correo = $_SESSION["correo"];//recojo el correo

    //recojo la fecha de hoy
    $fecha = date("Y-m-d");
    $fecha_unix = strtotime($fecha);
    $fecha_formateada = date("Y-m-d", $fecha_unix); 


    //inserto el correo del usuario y la fecha en que realiza el pedido en la tabla pedidos
    $operaciones = $operaciones && $db->query("INSERT INTO pedidos(Correo_usuario, Fecha_Pedido) VALUES('$correo', '$fecha_formateada');");
    //ahora hago una consulta para obtener con que id se ha asignado esa insercion
    
    $consultaID = $db->lastInsertId();

    /*Recorro el carrito */
    foreach($_SESSION["carrito"] as $producto){
        //obtengo la cantidad de stock resultante del producto
        $stock = $producto->stock - $producto->cantidad;
        $id_producto = $producto->id;//y el id del producto comprado
        $operaciones = $operaciones && $db->query("UPDATE productos SET stock= '$stock' WHERE Id_Producto = '$id_producto';");  //aqui actualizo el stock de cada producto
        $operaciones = $operaciones && $db->query("INSERT INTO detalle_pedido(Id_Pedido_detalle, ID_Producto_detalle, Cantidad_producto)
                                                    VALUES('". $consultaID." ', '$id_producto', '$producto->cantidad');");
        crearticket($_SESSION["correo"],$producto->nombre,$producto->precio,$producto->cantidad);
        $precio_final += $producto->precio * $producto->cantidad;
    }

    //ahora con el precio calculado compruebo si es superior al saldo, si lo es muestro un mensaje de error al usuario
    if($_SESSION["saldo"] < $precio_final){
        header("Location: carrito.php?error_saldo=true");
        exit;
    }


    $_SESSION["saldo"] -= $precio_final;
    $saldo = $_SESSION["saldo"];
    $correo = $_SESSION["correo"];
    $operaciones = $operaciones && $db->query("UPDATE usuarios SET saldo ='$saldo' WHERE Correo = '$correo';");

    //si todas las operaciones salen bien entonces actualizo la base de datos y elimino el carrito
    if ($operaciones){
        $db->commit();
        unset($_SESSION["carrito"]);
    }
    else{
        $db->rollback();
    }

}






/*Clase que utilizan los objetos del carrito para almacenar todos sus datos */
class articulo {
    public $id;
    public $nombre;
    public $descripcion;
    public $imagen;
    public $precio;
    public $cantidad;
    public $stock;

    public function __construct($id, $nombre, $descripcion, $imagen, $precio, $cantidad, $stock){
        $this->id = $id;
        $this->nombre = $nombre;
        $this->descripcion = $descripcion;
        $this->imagen = $imagen;
        $this->precio = $precio;
        $this->cantidad = $cantidad;
        $this->stock = $stock;
    }
}









/*Funcion para crear un ticket para el usuario */
function crearticket($usuario, $nombre, $precio, $cantidad){
    $banner = "
 _____ _     ___  ____  ___ ____ _____ _____ ____  ___    _      ____  _____ _____  _    _     ____  
|  ___| |   / _ \|  _ \|_ _/ ___|_   _| ____|  _ \|_ _|  / \    |  _ \| ____|_   _|/ \  | |   / ___| 
| |_  | |  | | | | |_) || |\___ \ | | |  _| | |_) || |  / _ \   | |_) |  _|   | | / _ \ | |   \___ \ 
|  _| | |__| |_| |  _ < | | ___) || | | |___|  _ < | | / ___ \  |  __/| |___  | |/ ___ \| |___ ___) |
|_|   |_____\___/|_| \_\___|____/ |_| |_____|_| \_\___/_/   \_\ |_|   |_____| |_/_/   \_\_____|____/ 
    \n";
    $date = date("Y-m-d H:i:s")."\n";
    $texto="NOMBRE PRODUCTO -> ".$nombre." PRECIO -> ". $precio." CANTIDAD -> ".$cantidad."\n";

    if (!file_exists("tickets/Ticket ". $usuario .".txt")) {
        $file = fopen("tickets/Ticket ". $usuario .".txt", "a+") or die("No se puede abrir el archivo!");
        fwrite($file, $banner);
        fwrite($file, $date);
        }else{
        $file = fopen("tickets/Ticket ". $usuario .".txt", "a+") or die("No se puede abrir el archivo!");
        fwrite($file, $texto);
        fclose($file);
        }

        //creamos ticket
        
   
}
?>