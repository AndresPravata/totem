<?php
  #Include the connect.php file
  include('Conexion.php');
 // include_once("../../includes/Iterador.php");
  $objCon = new Conexion();
	$link = $objCon->conectar();
// Starting the session

//    session_start();

  
  $bool = mysqli_select_db($link, $db);
  if ($bool === False){
	  print "can't find $db";
  }
  // get data and store in a json array
  $orders = [];
  $opcion = (isset($_POST['opcion'])) ? $_POST['opcion'] : 0;
  $nombre = (isset($_POST['name'])) ? $_POST['name'] : '';
  $telefono = (isset($_POST['telefono'])) ? $_POST['telefono'] : '';
  $fecha = (isset($_POST['fecha'])) ? $_POST['fecha'] : 0;
 
  ob_clean();
  switch($opcion){
    case 0:
          $query = "select * from productos where codigo like '$codigo' order by codigo;";
          $result = mysqli_query($link,$query) or die("SQL Error 1: " . mysqli_error($link));
          $i1 = new Iterador();
		      $productos = $i1->iterarObjetos($result);
          $query = "select * from codbarra where PRODUCTO = '$codigo';";
          $result = mysqli_query($link,$query) or die("SQL Error 1: " . mysqli_error($link));
          $fila = mysqli_fetch_array($result);
          if ($fila['CODBARRA'] != null)
            $productos[1]['barra'] = $fila['CODBARRA'];
          echo json_encode($productos);
          mysqli_free_result($result);
        break;
    case 1: // agrego productos nuevos
     
        $query = "INSERT INTO appointment (`name`, `date_sched`, `telefono`,`date_created` )
                   VALUES('{$nombre}','{$fecha}', {$telfono},CURRENT_TIMESTAMP())";  
                
        $result1 = mysqli_query($link, $query) or die("SQL Error 1: " . mysqli_error($link));
        $id_producto = mysqli_insert_id($link);
        echo $id_producto;
    break;
    case 2:
      $datos=$_POST['datos'];
      if ($datos['Imagen'] != ""){
        $imagen = $datos['Imagen'];
      } else {
        $imagen = "no_image.jpg";
      }
      $query = "UPDATE productos SET nombreprod='{$datos['descripcion']}', activo={$datos['activo']}, fm=CURRENT_TIMESTAMP(), precio = {$datos['precio']}, desc1 = {$datos['desc1']},
                contado = {$datos['desc3']}, desc2={$datos['desc2']}, preciocost={$datos['preciocosto']}, pedido={$datos['ptopedido']}, stock={$datos['stock']},proveedor='{$datos['proveedor']}',
                rubro={$datos['rubro']},cod_prov='{$datos['codigoprov']}', serie='{$datos['serie']}',iva={$datos['iva']},dolar={$datos['dolar']},portransporte={$datos['portransporte']},
                porib={$datos['porib']},marca={$datos['marca']},pedirpeso={$datos['pedirpeso']},web={$datos['web']},grupo='{$datos['grupo']}',Imagen='{$imagen}'";
      if ($datos['FechaAjusteStock'] != null)
                $query .= " ,FechaAjusteStock='{$datos['FechaAjusteStock']}'";
      $query .= " where codigo = '{$datos['codigo']}';";
      $result = mysqli_query($link, $query) or die("SQL Error 1: " . mysqli_error($link));
      if (isset($datos['barra'])){
          if ($datos['barra'] != "") {
              $query= "select * from codbarra where CODBARRA = '{$datos['barra']}' and PRODUCTO = '{$datos['codigo']}';";
              $result = mysqli_query($link, $query) or die("SQL Error 1: " . mysqli_error($link));
              if (mysqli_num_rows($result) == 0)
              { $query="insert into codbarra (CODBARRA,PRODUCTO) values ('{$datos['barra']}','{$datos['codigo']}');";
                $result = mysqli_query($link,$query) or die("SQL Error 1: " . mysqli_error($link));
              } else {
                $fila = mysqli_fetch_array($result);
                if ($fila['CODBARRA'] != $datos['barra']){
                  $query = "update codbarra set CODBARRA = '{$datos['barra']}' where PRODUCTO = '{$datos['codigo']}';";
                  $result = mysqli_query($link,$query) or die("SQL Error 1: " . mysqli_error($link));
                }
              }
          } else{
            // borro el código de barra del producto
            $query= "delete from codbarra where PRODUCTO = '{$datos['codigo']}';";
            $result = mysqli_query($link, $query) or die("SQL Error 1: " . mysqli_error($link));
          }
      }
      // veo si está relacionado con prestashop
      $query="select id_prestashop from productos where codigo = '{$datos['codigo']}';";
      $result = mysqli_query($link,$query) or die("SQL Error 1: " . mysqli_error($link));
      $fila = mysqli_fetch_array($result);
      if (($fila['id_prestashop'] != null) && ($fila['id_prestashop'] > 0)){
        // busco la lista de precios 1
        $id_producto = $fila['id_prestashop'];
        $query = "select * from listas where Codigo = '{$datos['codigo']}' and Lista = 1;";
        $result = mysqli_query($link,$query) or die("SQL Error 1: " . mysqli_error($link));
        $i1 = new Iterador();
        $lista = $i1->iterarObjetos($result);
        if($datos['iva'] == 21)
            $roliva= 53;
          else
            $roliva = 54;
        if ($datos['web'] == 1){
          if($datos['proveedor'] == null)
            $proveedor = 0;
          else
            $proveedor = $datos['proveedor'];
          
            $query = "update dis_product set id_supplier={$proveedor},id_manufacturer={$datos['marca']},id_category_default={$datos['rubro']},id_tax_rules_group={$roliva},
            quantity={$datos['stock']},price={$lista[1]['Precio_s_iva']},wholesale_price={$datos['preciocosto']},active={$datos['activo']},redirect_type='404',date_upd=CURRENT_TIMESTAMP(),
            ean13 ='{$datos['barra']}' where id_product = {$datos['id_prestashop']};";
            $result = mysqli_query($link, $query) or die("SQL Error 1: " . mysqli_error($link));
            
            $linkrewrite = str_replace(" ","_",$datos['descripcion']);
            // actualizo el nombre del producto dis_product_lang
            $query = "update dis_product_lang set name='{$datos['descripcion']}',description='{$datos['descripcion']}',link_rewrite='$linkrewrite' where id_product = {$datos['id_prestashop']};";
            $result = mysqli_query($link, $query) or die("SQL Error 1: " . mysqli_error($link));
            // agrego stock en el shop
            if ($datos['stock'] < 0)
              $stock = 0;
            else
              $stock = $datos['stock'];
            
            $query = "update dis_stock_available set quantity = {$stock} where id_product = {$datos['id_prestashop']};";
            $result = mysqli_query($link, $query) or die("SQL Error 1: " . mysqli_error($link));    

          }
        else{
          $query = "update dis_product set active={$datos['activo']} where id_product = {$datos['id_prestashop']};";
            $result = mysqli_query($link, $query) or die("SQL Error 1: " . mysqli_error($link));
            
        }
        
      } else{
        if ($datos['web'] == 1){
          if($datos['iva'] == 21)
          $roliva= 53;
        else
          $roliva = 54;
        // busco la lista de precios 1
        $query = "select * from listas where Codigo = '{$datos['codigo']}' and Lista = 1;";
        $result = mysqli_query($link,$query) or die("SQL Error 1: " . mysqli_error($link));
        $i1 = new Iterador();
        $lista = $i1->iterarObjetos($result);
        if($datos['proveedor'] == null)
            $proveedor = 0;
          else
            $proveedor = $datos['proveedor'];
        $query = "insert into dis_product (id_supplier,id_manufacturer,id_category_default,id_tax_rules_group,quantity,price,wholesale_price,active,redirect_type,date_add,date_upd,indexed,cache_default_attribute,ean13) values
        ({$proveedor},{$datos['marca']},{$datos['rubro']},{$roliva},{$datos['stock']},{$lista[1]['Precio_s_iva']},{$datos['preciocosto']},{$datos['activo']},'404',CURRENT_TIMESTAMP(),CURRENT_TIMESTAMP(),1,0,'{$datos['barra']}');";
        $result = mysqli_query($link, $query) or die("SQL Error 1: " . mysqli_error($link));
        $id_producto = mysqli_insert_id($link);
        $query = "update productos set id_prestashop = {$id_producto} where codigo = '{$datos['codigo']}';";
        $result = mysqli_query($link,$query) or die("SQL Error 1: " . mysqli_error($link));
        
        // veo los lenguajes que hay
        $linkrewrite = str_replace(" ","_",$datos['descripcion']);
        $query = "select id_lang from dis_lang;";
        $result = mysqli_query($link, $query) or die("SQL Error 1: " . mysqli_error($link));
        $i1 = new Iterador();
        $lenguajes = $i1->iterarObjetos($result);
        foreach ($lenguajes as $l){
            // inserto el nombre del producto dis_product_lang
            $query = "INSERT INTO dis_product_lang (id_product, id_lang, name,description,link_rewrite) VALUES($id_producto, {$l['id_lang']}, '{$datos['descripcion']}','{$datos['descripcion']}','$linkrewrite');";
            $result = mysqli_query($link, $query) or die("SQL Error 1: " . mysqli_error($link));
            
        }
        // inserto el producto en el shop dis_product_shop
        $query = "INSERT INTO dis_product_shop (id_product, id_shop, id_category_default,id_tax_rules_group,price,wholesale_price,active,redirect_type,cache_default_attribute,date_add,date_upd,indexed) VALUES
                                                ($id_producto, 1,{$datos['rubro']},$roliva,{$lista[1]['Precio_s_iva']},{$datos['preciocosto']},1,'404',0,CURRENT_TIMESTAMP(),CURRENT_TIMESTAMP(),1);";
        $result = mysqli_query($link, $query) or die("SQL Error 1: " . mysqli_error($link));
        $query = "select max(position) as lugar from dis_category_product where id_category = {$datos['rubro']};";
        $result = mysqli_query($link, $query) or die("SQL Error 1: " . mysqli_error($link));
        $i1 = new Iterador();
        $lugares = $i1->iterarObjetos($result);
        if ($lugares[1]['lugar'] == null)
            $lugar = 0;
          else
            $lugar = intval($lugares[1]['lugar'])+1;
        $query = "INSERT INTO dis_category_product (id_category,id_product, position) VALUES ({$datos['rubro']},$id_producto, $lugar);";
        $result = mysqli_query($link, $query) or die("SQL Error 1: " . mysqli_error($link));    
        // agrego stock en el shop
        if ($datos['stock'] < 0)
              $stock = 0;
            else
              $stock = $datos['stock'];
        $query = "INSERT INTO dis_stock_available (id_product,id_product_attribute,id_shop,id_shop_group,quantity) VALUES ($id_producto, 0,1,0,{$stock});";
        $result = mysqli_query($link, $query) or die("SQL Error 1: " . mysqli_error($link));    

        }
      }
      // agrego imagen
      if ($datos['Imagen'] != ""){
        
        // subo imagen via webservice
        $urlImage = 'http://distrisur.net.ar/api/images/products/'.$id_producto.'/';
        $key  = 'NM7UV1YYBKUABICGK79HACX8YMUVMAEF';

        //Here you set the path to the image you need to upload
        $image_path = '../../uploads/productos/'.$id_producto.".jpg";
        $image_mime = 'image/jpg';

        $args['image'] = new CurlFile($image_path, $image_mime);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLINFO_HEADER_OUT, 1);
        curl_setopt($ch, CURLOPT_URL, $urlImage);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_USERPWD, $key.':');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $args);
        $resultado = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if (200 == $httpCode) {
            echo 'Product image was successfully created.';
        }
      }
      ob_clean();
      echo $result;  
      
    break;
    case 3: // eliminar artículo       
      $query = "Select * from predet where idproducto = '{$codigo}';";
      $result = mysqli_query($link,$query) or die("SQL Error 1: " . mysqli_error($link));
      if (mysqli_num_rows($result) == 0 || $seguir)
      {
        // veo el codigo en prestashop
        $query = "Select * from productos where codigo = '{$codigo}';";
        $result = mysqli_query($link,$query) or die("SQL Error 1: " . mysqli_error($link));
        $producto = mysqli_fetch_array($result);
        $id_prestashop = $producto['id_prestashop'];
        $imagen = $producto['imagen'];
        // elimino codigo de barra en codbarra
        $query = "delete from codbarra WHERE PRODUCTO = '{$codigo}';";
        $result1 = mysqli_query($link, $query) or die("SQL Error 1: " . mysqli_error($link));
        // elimino precios en listas
        $query = "delete from listas where Codigo = '{$codigo}';";
        $result1 = mysqli_query($link, $query) or die("SQL Error 1: " . mysqli_error($link));
        // elimino los datos del producto de prstashop
        if ($id_prestashop != 0){
          define('DEBUG', true);
          define('PS_SHOP_PATH', 'http://distrisur.net.ar/');
          define('PS_WS_AUTH_KEY', 'NM7UV1YYBKUABICGK79HACX8YMUVMAEF');
          require_once('../../includes/PSWebServiceLibrary.php');
          try
          {
            $webService = new PrestaShopWebservice(PS_SHOP_PATH, PS_WS_AUTH_KEY, DEBUG);
            // Call for a deletion, we specify the resource name and the id of the resource in order to delete the item
            $webService->delete(array('resource' => 'products', 'id' => intval($id_prestashop)));
            // If there's an error we throw an exception
            echo 'Successfully deleted !<meta http-equiv="refresh" content="5"/>';
          }
          catch (PrestaShopWebserviceException $e)
          {
            // Here we are dealing with errors
            $trace = $e->getTrace();
            if ($trace[0]['args'][0] == 404) echo 'Bad ID';
            else if ($trace[0]['args'][0] == 401) echo 'Bad auth key';
            else echo 'Other error<br />'.$e->getMessage();
          }          
        }
        ob_clean();
        // elimino el producto de productos
        $query = "delete from productos where codigo = '{$codigo}';";
        $result = mysqli_query($link, $query) or die("SQL Error 1: " . mysqli_error($link));
        echo $result;
      }
      else
      {
        $orders['error'] = 'ESTE PRODUCTO YA SE HA VENDIDO';
        echo json_encode($orders);
        mysqli_free_result($result);
      }
      
      break;
    case 4:
        $query = "select * from productos where codigo = '$codigo' order by codigo;";
        $result = mysqli_query($link,$query) or die("SQL Error 1: " . mysqli_error($link));
      
        echo mysqli_num_rows($result);
        mysqli_free_result($result);
      break;
    case 5:
        $query = "select * from listas where Codigo = '$codigo' and Lista = $lista;";
        $result = mysqli_query($link,$query) or die("SQL Error 1: " . mysqli_error($link));
      
        echo mysqli_num_rows($result);
        mysqli_free_result($result);
      break;
    case 6:
        $datos=$_POST['datos'];
        $query = "INSERT INTO listas (`Codigo`, `Lista`, `Precio`, `Porcentaje`, `Precio_s_iva`) VALUES
            ('{$_POST['producto']}',{$_POST['lista']}, 0,0,0);";
                
        $result = mysqli_query($link, $query) or die("SQL Error 1: " . mysqli_error($link));
        echo $result;
        break;  
      case 7: //agregar registro proveedor - producto
          $codigo = 0;
          
          if ($_POST['estado'] == 2){ //editando un producto
            $query = "select id_prestashop, web from productos where codigo = '{$_POST['producto']}';";
            $result = mysqli_query($link, $query) or die("SQL Error 1: " . mysqli_error($link));
            $producto = mysqli_fetch_array($result);
            if ($producto['id_prestashop'] != 0)
              $codigo = $producto['id_prestashop'];
            else {
              if ($producto['web']){

              }
              
            }
          }
          if ($codigo == 0){
              $query = "select min(id_prestashop)-1 as minid from productos where id_prestashop <= 0;";
              $result = mysqli_query($link, $query) or die("SQL Error 1: " . mysqli_error($link));
              $lectura = mysqli_fetch_array($result);
              if (($lectura['minid'] != null) && ($lectura['minid'] != ""))
                $codigo = $lectura['minid'];
              else
                $codigo = -1;
              
          }
          if ($_POST['estado'] == 2)
          {
            $query = "update productos set id_prestashop = {$codigo} where codigo = '{$_POST['producto']}';";
            $result = mysqli_query($link,$query) or die("SQL Error 1: " . mysqli_error($link));
          }
          $query = "select * from dis_product_supplier where id_product = {$codigo} and id_supplier is null;";
              $result = mysqli_query($link, $query) or die("SQL Error 1: " . mysqli_error($link));
          if(mysqli_num_rows($result) == 0){              
                    $query = "INSERT INTO dis_product_supplier (`id_product`) VALUES
                        ('{$codigo}');";
                            
                    $result = mysqli_query($link, $query) or die("SQL Error 1: " . mysqli_error($link));
          }
          $query = "(SELECT ps.*, s.name FROM dis_product_supplier ps, dis_supplier s, productos p where p.codigo ='{$_POST['producto']}' and id_product = p.codigo and s.id_supplier = ps.id_supplier)union";
          $query .= "(SELECT ps.*, '' as name FROM dis_product_supplier ps, dis_supplier s,productos p where ps.id_product = '{$codigo}' and ps.id_supplier is null) union";
          $query .= "(SELECT ps.*, s.name FROM dis_product_supplier ps, dis_supplier s, productos p where ps.id_product ='{$codigo}' and s.id_supplier = ps.id_supplier)";
          $query .= "ORDER By name";
          $resultado = mysqli_query($link,$query) or die("SQL Error 1: " . mysqli_error($link));

          
            

            while ($fila = mysqli_fetch_array($resultado)) {
              $salida.="<tr>
                              <td>".$fila['id_product_supplier']."</td>
                              <td>".$fila['name']."</td>
                              <td>".$fila['product_supplier_reference']."</td>
                              <td>".$fila['product_supplier_price_te']."</td>
                    </tr>";

            }
            
            ob_clean();
$datos['producto']=$codigo;
$datos['salida']=$salida;
          echo json_encode($datos);
          break;  
  }

  
?>