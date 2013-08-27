<div class='alert-message alert-error Cart66AjaxMessage'>
  <p><strong><?php echo $data['errorMessage']; ?></strong></p>

  <?php 
  if(is_array($data['exception'])) {
    if(isset($data['exception']['error_code'])) {
      echo $data['exception']['error_code'];
      unset($data['exception']['error_code']);
    }
    echo '<ul>';
    foreach($data['exception'] as $exception) {
      echo "<li>$exception</li>";
    }
    echo '</ul>';
  }
  else {
    echo $data['exception'];
  } ?>
</div>