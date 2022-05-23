<?php
  require_once('includes/load.php');
  // Checkin What level user has permission to view this page
  page_require_level(3);
?>
<?php
  $d_buy = find_by_id('entradas',(int)$_GET['id']);
  if(!$d_buy){
    $session->msg("d","ID vacío.");
    redirect('buy.php');
  }
?>
<?php
  $delete_id = delete_by_id('entradas',(int)$d_buy['id']);
  if($delete_id){
      $session->msg("s","Entrada eliminada.");
      redirect('buy.php');
  } else {
      $session->msg("d","Eliminación falló");
      redirect('buy.php');
  }
?>
