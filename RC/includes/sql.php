<?php
  require_once('includes/load.php');

/*--------------------------------------------------------------*/
/* Function for find all database table rows by table name
/*--------------------------------------------------------------*/
function find_all($table) {
   global $db;
   if(tableExists($table))
   {
     return find_by_sql("SELECT * FROM ".$db->escape($table));
   }
}
/*--------------------------------------------------------------*/
/* Function for Perform queries
/*--------------------------------------------------------------*/
function find_by_sql($sql)
{
  global $db;
  $result = $db->query($sql);
  $result_set = $db->while_loop($result);
 return $result_set;
}
/*--------------------------------------------------------------*/
/*  Function for Find data from table by id
/*--------------------------------------------------------------*/
function find_by_id($table,$id)
{
  global $db;
  $id = (int)$id;
    if(tableExists($table)){
          $sql = $db->query("SELECT * FROM {$db->escape($table)} WHERE id='{$db->escape($id)}' LIMIT 1");
          if($result = $db->fetch_assoc($sql))
            return $result;
          else
            return null;
     }
}
/*--------------------------------------------------------------*/
/* Function for Delete data from table by id
/*--------------------------------------------------------------*/
function delete_by_id($table,$id)
{
  global $db;
  if(tableExists($table))
   {
    $sql = "DELETE FROM ".$db->escape($table);
    $sql .= " WHERE id=". $db->escape($id);
    $sql .= " LIMIT 1";
    $db->query($sql);
    return ($db->affected_rows() === 1) ? true : false;
   }
}
/*--------------------------------------------------------------*/
/* Function for Count id  By table name
/*--------------------------------------------------------------*/

function count_by_id($table){
  global $db;
  if(tableExists($table))
  {
    $sql    = "SELECT COUNT(id) AS total FROM ".$db->escape($table);
    $result = $db->query($sql);
     return($db->fetch_assoc($result));
  }
}
/*--------------------------------------------------------------*/
/* Determine if database table exists
/*--------------------------------------------------------------*/
function tableExists($table){
  global $db;
  $table_exit = $db->query('SHOW TABLES FROM '.DB_NAME.' LIKE "'.$db->escape($table).'"');
      if($table_exit) {
        if($db->num_rows($table_exit) > 0)
              return true;
         else
              return false;
      }
  }
 /*--------------------------------------------------------------*/
 /* Login with the data provided in $_POST,
 /* coming from the login form.
/*--------------------------------------------------------------*/
  function authenticate($username='', $password='') {
    global $db;
    $username = $db->escape($username);
    $password = $db->escape($password);
    $sql  = sprintf("SELECT id,username,password,usuario_nivel FROM usuarios WHERE username ='%s' LIMIT 1", $username);
    $result = $db->query($sql);
    if($db->num_rows($result)){
      $user = $db->fetch_assoc($result);
      $password_request = sha1($password);
      if($password_request === $user['password'] ){
        return $user['id'];
      }
    }
   return false;
  }
  /*--------------------------------------------------------------*/
  /* Login with the data provided in $_POST,
  /* coming from the login_v2.php form.
  /* If you used this method then remove authenticate function.
 /*--------------------------------------------------------------*/
   function authenticate_v2($username='', $password='') {
     global $db;
     $username = $db->escape($username);
     $password = $db->escape($password);
     $sql  = sprintf("SELECT id,username,password,user_level FROM users WHERE username ='%s' LIMIT 1", $username);
     $result = $db->query($sql);
     if($db->num_rows($result)){
       $user = $db->fetch_assoc($result);
       $password_request = sha1($password);
       if($password_request === $user['password'] ){
         return $user;
       }
     }
    return false;
   }


  /*--------------------------------------------------------------*/
  /* Find current log in user by session id
  /*--------------------------------------------------------------*/
  function current_user(){
      static $current_user;
      global $db;
      if(!$current_user){
         if(isset($_SESSION['user_id'])):
             $user_id = intval($_SESSION['user_id']);
             $current_user = find_by_id('usuarios',$user_id);
        endif;
      }
    return $current_user;
  }
  /*--------------------------------------------------------------*/
  /* Find all user by
  /* Joining users table and user gropus table
  /*--------------------------------------------------------------*/
  function find_all_user(){
      global $db;
      $results = array();
      $sql = "SELECT u.id,u.nombre,u.username,u.usuario_nivel,u.status,u.ultimo_login,";
      $sql .="g.grupo_nombre ";
      $sql .="FROM usuarios u ";
      $sql .="LEFT JOIN usuarios_grupos g ";
      $sql .="ON g.grupo_nivel=u.usuario_nivel ORDER BY u.nombre ASC";
      $result = find_by_sql($sql);
      return $result;
  }
  /*--------------------------------------------------------------*/
  /* Function to update the last log in of a user
  /*--------------------------------------------------------------*/

 function updateLastLogIn($user_id)
	{
		global $db;
    $date = make_date();
    $sql = "UPDATE usuarios SET ultimo_login='{$date}' WHERE id ='{$user_id}' LIMIT 1";
    $result = $db->query($sql);
    return ($result && $db->affected_rows() === 1 ? true : false);
	}

  /*--------------------------------------------------------------*/
  /* Find all Group name
  /*--------------------------------------------------------------*/
  function find_by_groupName($val)
  {
    global $db;
    $sql = "SELECT grupo_nombre FROM usuarios_grupos WHERE grupo_nombre = '{$db->escape($val)}' LIMIT 1 ";
    $result = $db->query($sql);
    return($db->num_rows($result) === 0 ? true : false);
  }
  /*--------------------------------------------------------------*/
  /* Find group level
  /*--------------------------------------------------------------*/
  function find_by_groupLevel($level)
  {
    global $db;
    $sql = "SELECT grupo_nivel FROM usuarios_grupos WHERE grupo_nivel = '{$db->escape($level)}' LIMIT 1 ";
    $result = $db->query($sql);
    return($db->num_rows($result) === 0 ? true : false);
  }
  /*--------------------------------------------------------------*/
  /* Function for cheaking which user level has access to page
  /*--------------------------------------------------------------*/
   function page_require_level($require_level){
     global $session;
     $current_user = current_user();
     $login_level = find_by_groupLevel($current_user['usuario_nivel']);
     //if user not login
     if (!$session->isUserLoggedIn(true)):
            $session->msg('d','Por favor Iniciar sesión...');
            redirect('index.php', false);
      //if Group status Deactive
     elseif($login_level['grupo_status'] === '0'):
           $session->msg('d','Este nivel de usaurio esta inactivo!');
           redirect('home.php',false);
      //cheackin log in User level and Require level is Less than or equal to
     elseif($current_user['usuario_nivel'] <= (int)$require_level):
              return true;
      else:
            $session->msg("d", "¡Lo siento!  no tienes permiso para ver la página.");
            redirect('home.php', false);
        endif;

     }
   /*--------------------------------------------------------------*/
   /* Function for Finding all product name
   /* JOIN with categorie  and media database table
   /*--------------------------------------------------------------*/
  function join_product_table(){
     global $db;
     $sql  =" SELECT p.id,p.nombre,p.cantidad,p.compra_precio,p.venta_precio,p.media_id,p.fecha,c.nombre";
    $sql  .=" AS ubicaciones,m.file_name AS image";
    $sql  .=" FROM productos p";
    $sql  .=" LEFT JOIN ubicaciones c ON c.id = p.categoria_id";
    $sql  .=" LEFT JOIN media m ON m.id = p.media_id";
    $sql  .=" ORDER BY p.fecha DESC";
    return find_by_sql($sql);

   }
  /*--------------------------------------------------------------*/
  /* Function for Finding all product name
  /* Request coming from ajax.php for auto suggest
  /*--------------------------------------------------------------*/

   function find_product_by_title($product_name){
     global $db;
     $p_name = remove_junk($db->escape($product_name));
     $sql = "SELECT nombre FROM productos WHERE nombre like '%$p_name%' LIMIT 5";
     $result = find_by_sql($sql);
     return $result;
   }

  /*--------------------------------------------------------------*/
  /* Function for Finding all product info by product title
  /* Request coming from ajax.php
  /*--------------------------------------------------------------*/
  function find_all_product_info_by_title($title){
    global $db;
    $sql  = "SELECT * FROM productos ";
    $sql .= " WHERE nombre ='{$title}'";
    $sql .=" LIMIT 1";
    return find_by_sql($sql);
  }

  /*--------------------------------------------------------------*/
  /* Function for Update product quantity
  /*--------------------------------------------------------------*/
  function update_product_qty($qty,$p_id){
    global $db;
    $qty = (int) $qty;
    $id  = (int)$p_id;
    $sql = "UPDATE productos SET cantidad=cantidad -'{$qty}' WHERE id = '{$id}'";
    $result = $db->query($sql);
    return($db->affected_rows() === 1 ? true : false);

  } 
  /*--------------------------------------------------------------*/
  /* Function for Update product quantity buy
  /*--------------------------------------------------------------*/
  function update_product_qty_buy($qty,$p_id){
    global $db;
    $qty = (int) $qty;
    $id  = (int)$p_id;
    $sql = "UPDATE productos SET cantidad=cantidad +'{$qty}' WHERE id = '{$id}'";
    $result = $db->query($sql);
    return($db->affected_rows() === 1 ? true : false);

  }
  /*--------------------------------------------------------------*/
  /* Function for Display Recent product Added
  /*--------------------------------------------------------------*/
 function find_recent_product_added($limit){
   global $db;
   $sql   = " SELECT p.id,p.nombre,p.venta_precio,p.media_id,c.nombre AS ubicaciones,";
   $sql  .= "m.file_name AS image FROM productos p";
   $sql  .= " LEFT JOIN ubicaciones c ON c.id = p.categoria_id";
   $sql  .= " LEFT JOIN media m ON m.id = p.media_id";
   $sql  .= " ORDER BY p.id DESC LIMIT ".$db->escape((int)$limit);
   return find_by_sql($sql);
 }
 /*--------------------------------------------------------------*/
 /* Function for Find Highest saleing Product
 /*--------------------------------------------------------------*/
 function find_higest_saleing_product($limit){
   global $db;
   $sql  = "SELECT p.nombre, COUNT(s.producto_id) AS totalSold, SUM(s.cantidad) AS totalQty";
   $sql .= " FROM ventas s";
   $sql .= " LEFT JOIN productos p ON p.id = s.producto_id ";
   $sql .= " GROUP BY s.producto_id";
   $sql .= " ORDER BY SUM(s.cantidad) DESC LIMIT ".$db->escape((int)$limit);
   return $db->query($sql);
 }
 /*--------------------------------------------------------------*/
 /* Function for find all sales
 /*--------------------------------------------------------------*/
 function find_all_sale(){
   global $db;
   $sql  = "SELECT s.id,s.cantidad,s.precio,s.fecha,p.nombre";
   $sql .= " FROM ventas s";
   $sql .= " LEFT JOIN productos p ON s.producto_id = p.id";
   $sql .= " ORDER BY s.fecha DESC";
   return find_by_sql($sql);
   
 }
  /*--------------------------------------------------------------*/
 /* Function for find all buys
 /*--------------------------------------------------------------*/
 function find_all_buy(){
  global $db;
  $sql  = "SELECT b.id,b.cantidad,b.precio,b.fecha,p.nombre";
  $sql .= " FROM entradas b";
  $sql .= " LEFT JOIN productos p ON b.producto_id = p.id";
  $sql .= " ORDER BY b.fecha DESC";
  return find_by_sql($sql);
}
 /*--------------------------------------------------------------*/
 /* Function for Display Recent sale
 /*--------------------------------------------------------------*/
function find_recent_sale_added($limit){
  global $db;
  $sql  = "SELECT s.id,s.cantidad,s.precio,s.fecha,p.nombre";
  $sql .= " FROM ventas s";
  $sql .= " LEFT JOIN productos p ON s.producto_id = p.id";
  $sql .= " ORDER BY s.fecha DESC LIMIT ".$db->escape((int)$limit);
  return find_by_sql($sql);
}
/*--------------------------------------------------------------*/
/* Function for Generate sales report by two dates
/*--------------------------------------------------------------*/
function find_sale_by_dates($start_date,$end_date){
  global $db;
  $start_date  = date("Y-m-d", strtotime($start_date));
  $end_date    = date("Y-m-d", strtotime($end_date));
  $sql  = "SELECT s.fecha, p.nombre,p.venta_precio,p.compra_precio,";
  $sql .= "COUNT(s.producto_id) AS total_records,";
  $sql .= "SUM(s.cantidad) AS total_sales,";
  $sql .= "SUM(p.venta_precio * s.cantidad) AS total_saleing_price,";
  $sql .= "SUM(p.compra_precio * s.cantidad) AS total_buying_price ";
  $sql .= "FROM ventas s ";
  $sql .= "LEFT JOIN productos p ON s.producto_id = p.id";
  $sql .= " WHERE s.fecha BETWEEN '{$start_date}' AND '{$end_date}'";
  $sql .= " GROUP BY DATE(s.fecha),p.nombre";
  $sql .= " ORDER BY DATE(s.fecha) DESC";
  return $db->query($sql);
}
/*--------------------------------------------------------------*/
/* Function for Generate buys report by two dates
/*--------------------------------------------------------------*/
function find_buy_by_dates($start_date,$end_date){
  global $db;
  $start_date  = date("Y-m-d", strtotime($start_date));
  $end_date    = date("Y-m-d", strtotime($end_date));
  $sql  = "SELECT b.fecha, p.nombre,p.venta_precio,p.compra_precio,";
  $sql .= "COUNT(b.producto_id) AS total_records,";
  $sql .= "SUM(b.cantidad) AS total_sales,";
  $sql .= "SUM(p.venta_precio * b.cantidad) AS total_saleing_price,";
  $sql .= "SUM(p.compra_precio * b.cantidad) AS total_buying_price ";
  $sql .= "FROM entradas b ";
  $sql .= "LEFT JOIN productos p ON b.producto_id = p.id";
  $sql .= " WHERE b.fecha BETWEEN '{$start_date}' AND '{$end_date}'";
  $sql .= " GROUP BY DATE(b.fecha),p.nombre";
  $sql .= " ORDER BY DATE(b.fecha) DESC";
  return $db->query($sql);
}
/*--------------------------------------------------------------*/
/* Function for Generate Daily sales report
/*--------------------------------------------------------------*/
function  dailySales($year,$month){
  global $db;
  $sql  = "SELECT s.cantidad,";
  $sql .= " DATE_FORMAT(s.fecha, '%Y-%m-%e') AS fecha,p.nombre,";
  $sql .= "SUM(p.venta_precio * s.cantidad) AS total_saleing_price";
  $sql .= " FROM ventas s";
  $sql .= " LEFT JOIN productos p ON s.producto_id = p.id";
  $sql .= " WHERE DATE_FORMAT(s.fecha, '%Y-%m' ) = '{$year}-{$month}'";
  $sql .= " GROUP BY DATE_FORMAT( s.fecha,  '%e' ),s.producto_id";
  return find_by_sql($sql);
} 
/*--------------------------------------------------------------*/
/* Function for Generate Daily buy report
/*--------------------------------------------------------------*/
function  dailyBuys($year,$month){
  global $db;
  $sql  = "SELECT b.cantidad,";
  $sql .= " DATE_FORMAT(b.fecha, '%Y-%m-%e') AS fecha,p.nombre,";
  $sql .= "SUM(p.venta_precio * b.cantidad) AS total_buying_price";
  $sql .= " FROM entradas b";
  $sql .= " LEFT JOIN productos p ON b.producto_id = p.id";
  $sql .= " WHERE DATE_FORMAT(b.fecha, '%Y-%m' ) = '{$year}-{$month}'";
  $sql .= " GROUP BY DATE_FORMAT( b.fecha,  '%e' ),b.producto_id";
  return find_by_sql($sql);
}
/*--------------------------------------------------------------*/
/* Function for Generate Monthly sales report
/*--------------------------------------------------------------*/
function  monthlySales($year){
  global $db;
  $sql  = "SELECT s.cantidad,";
  $sql .= " DATE_FORMAT(s.fecha, '%Y-%m-%e') AS fecha,p.nombre,";
  $sql .= "SUM(p.venta_precio * s.cantidad) AS total_saleing_price";
  $sql .= " FROM ventas s";
  $sql .= " LEFT JOIN productos p ON s.producto_id = p.id";
  $sql .= " WHERE DATE_FORMAT(s.fecha, '%Y' ) = '{$year}'";
  $sql .= " GROUP BY DATE_FORMAT( s.fecha,  '%c' ),s.producto_id";
  $sql .= " ORDER BY date_format(s.fecha, '%c' ) DESC";
  return find_by_sql($sql);
}
/*--------------------------------------------------------------*/
/* Function for Generate Monthly buys report
/*--------------------------------------------------------------*/
function  monthlyBuys($year){
  global $db;
  $sql  = "SELECT b.cantidad,";
  $sql .= " DATE_FORMAT(b.fecha, '%Y-%m-%e') AS fecha,p.nombre,";
  $sql .= "SUM(p.venta_precio * b.cantidad) AS total_buying_price";
  $sql .= " FROM entradas b";
  $sql .= " LEFT JOIN productos p ON b.producto_id = p.id";
  $sql .= " WHERE DATE_FORMAT(b.fecha, '%Y' ) = '{$year}'";
  $sql .= " GROUP BY DATE_FORMAT( b.fecha,  '%c' ),b.producto_id";
  $sql .= " ORDER BY date_format(b.fecha, '%c' ) DESC";
  return find_by_sql($sql);
}

?>
