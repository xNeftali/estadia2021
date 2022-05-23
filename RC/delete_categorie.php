<?php
  require_once('includes/load.php');
  // Checkin What level user has permission to view this page
  page_require_level(1);
?>
<?php
  $categorie = find_by_id('ubicaciones',(int)$_GET['id']);
  if(!$categorie){
    $session->msg("d","ID de la ubicacion falta.");
    redirect('categorie.php');
  }
?>
<?php
  $delete_id = delete_by_id('ubicaciones',(int)$categorie['id']);
  if($delete_id){
      $session->msg("s","Ubicacion eliminada");
      redirect('categorie.php');
  } else {
      $session->msg("d","Eliminación falló");
      redirect('categorie.php');
  }
?>
