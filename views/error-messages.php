<div class='Cart66Error Cart66AjaxMessage'>
  <p><strong><?php echo $data['errorMessage']; ?></strong></p>
  <p><?php 
  if(is_array($data['exception'])) {
    foreach($data['exception'] as $exception) {
      echo "<li>$exception</li>";
    }
  }
  else {
    echo $data['exception'];
  } ?></p>
</div>