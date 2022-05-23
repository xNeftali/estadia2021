<?php
  $page_title = 'Editar entrada';
  require_once('includes/load.php');
  // Checkin What level user has permission to view this page
   page_require_level(3);
?>
<?php
$buy = find_by_id('entradas',(int)$_GET['id']);
if(!$buy){
  $session->msg("d","Missing product id.");
  redirect('buy.php');
}
?>
<?php $product = find_by_id('productos',$buy['producto_id']); ?>
<?php

  if(isset($_POST['update_buy'])){
    $req_fields = array('title','quantity','price','total', 'date' );
    validate_fields($req_fields);
        if(empty($errors)){
          $p_id      = $db->escape((int)$product['id']);
          $b_qty     = $db->escape((int)$_POST['quantity']);
          $b_total   = $db->escape($_POST['total']);
          $date      = $db->escape($_POST['date']);
          $b_date    = date("Y-m-d", strtotime($date));

          $sql  = "UPDATE entradas SET";
          $sql .= " producto_id= '{$p_id}',cantidad={$b_qty},precio='{$b_total}',fecha='{$b_date}'";
          $sql .= " WHERE id ='{$buy['id']}'";
          $result = $db->query($sql);
          if( $result && $db->affected_rows() === 1){
                    update_product_qty_buy($b_qty,$p_id);
                    $session->msg('s',"Entrada actualizada.");
                    redirect('edit_buy.php?id='.$buy['id'], false);
                  } else {
                    $session->msg('d',' Lo siento, fallo la actualizacion!');
                    redirect('buy.php', false);
                  }
        } else {
           $session->msg("d", $errors);
           redirect('edit_buy.php?id='.(int)$buy['id'],false);
        }
  }

?>
<?php include_once('layouts/header.php'); ?>
<div class="row">
  <div class="col-md-6">
    <?php echo display_msg($msg); ?>
  </div>
</div>
<div class="row">

  <div class="col-md-12">
  <div class="panel">
    <div class="panel-heading clearfix">
      <strong>
        <span class="glyphicon glyphicon-th"></span>
        <span>Entradas</span>
     </strong>
     <div class="pull-right">
       <a href="buy.php" class="btn btn-primary">Ver todas las entradas</a>
     </div>
    </div>
    <div class="panel-body">
       <table class="table table-bordered">
         <thead>
          <th> Producto </th>
          <th> Cantidad </th>
          <th> Precio </th>
          <th> Total </th>
          <th> Fecha</th>
          <th> Acciones</th>
         </thead>
           <tbody  id="product_info">
              <tr>
              <form method="post" action="edit_buy.php?id=<?php echo (int)$buy['id']; ?>">
                <td id="b_name">
                  <input type="text" class="form-control" id="sug_input" name="title" value="<?php echo remove_junk($product['nombre']); ?>">
                  <div id="result" class="list-group"></div>
                </td>
                <td id="b_qty">
                  <input type="text" class="form-control" name="quantity" value="<?php echo (int)$buy['cantidad']; ?>">
                </td>
                <td id="b_price">
                  <input type="text" class="form-control" name="price" value="<?php echo remove_junk($product['venta_precio']); ?>" >
                </td>
                <td>
                  <input type="text" class="form-control" name="total" value="<?php echo remove_junk($buy['precio']); ?>">
                </td>
                <td id="b_date">
                  <input type="date" class="form-control datepicker" name="date" data-date-format="" value="<?php echo remove_junk($buy['fecha']); ?>">
                </td>
                <td>
                  <button type="submit" name="update_buy" class="btn btn-primary">Actualizar</button>
                </td>
              </form>
              </tr>
           </tbody>
       </table>

    </div>
  </div>
  </div>

</div>

<?php include_once('layouts/footer.php'); ?>
