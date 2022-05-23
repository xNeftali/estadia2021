<?php
  $page_title = 'Agregar entrada';
  require_once('includes/load.php');
  // Checkin What level user has permission to view this page
   page_require_level(3);
?>
<?php

  if(isset($_POST['add_sale'])){
    $req_fields = array('b_id','quantity','price','total', 'date' );
    validate_fields($req_fields);
        if(empty($errors)){
          $p_id      = $db->escape((int)$_POST['b_id']);
          $b_qty     = $db->escape((int)$_POST['quantity']);
          $b_total   = $db->escape($_POST['total']);
          $date      = $db->escape($_POST['date']);
          $b_date    = make_date();

          $sql  = "INSERT INTO entradas (";
          $sql .= " producto_id,cantidad,precio,fecha";
          $sql .= ") VALUES (";
          $sql .= "'{$p_id}','{$b_qty}','{$b_total}','{$b_date}'";
          $sql .= ")";

                if($db->query($sql)){
                  update_product_qty_buy($b_qty,$p_id);
                  $session->msg('s',"Entrada agregada ");
                  redirect('add_buy.php', false);
                } else {
                  $session->msg('d','Lo siento, registro falló.');
                  redirect('add_buy.php', false);
                }
        } else {
           $session->msg("d", $errors);
           redirect('add_buy.php',false);
        }
  }

?>
<?php include_once('layouts/header.php'); ?>
<div class="row">
  <div class="col-md-6">
    <?php echo display_msg($msg); ?>
    <form method="post" action="ajax.php" autocomplete="off" id="sug-form">
        <div class="form-group">
          <div class="input-group">
            <span class="input-group-btn">
              <button type="submit" class="btn btn-primary">Búsqueda</button>
            </span>
            <input type="text" id="sug_input" class="form-control" name="title"  placeholder="Buscar por el nombre del producto">
         </div>
         <div id="result" class="list-group"></div>
        </div>
    </form>
  </div>
</div>
<div class="row">

  <div class="col-md-12">
    <div class="panel panel-default">
      <div class="panel-heading clearfix">
        <strong>
          <span class="glyphicon glyphicon-th"></span>
          <span>Agregar entrada</span>
       </strong>
      </div>
      <div class="panel-body">
        <form method="post" action="add_buy.php">
         <table class="table table-bordered">
           <thead>
            <th> Producto </th>
            <th> Precio </th>
            <th> Cantidad </th>
            <th> Total </th>
            <th> Agregado</th>
            <th> Acciones</th>
           </thead>
             <tbody  id="product_info"> </tbody>
         </table>
       </form>
      </div>
    </div>
  </div>

</div>

<?php include_once('layouts/footer.php'); ?>
