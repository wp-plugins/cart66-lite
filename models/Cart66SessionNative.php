<?php
class Cart66SessionNative {
  
  public static function setMaxLifetime($minutes) {
    
  }
  
  public static function touch() {
    
  }
  
  public static function set($key, $value, $forceSave=false) {
    $_SESSION['cart66'][$key] = $value;
  }
  
  public static function drop($key, $forceSave=false) {
    unset($_SESSION['cart66'][$key]);
  }
  
  public static function get($key) {
    $value = false;
    if(isset($_SESSION['cart66'][$key])) {
      $value = $_SESSION['cart66'][$key];
    }
    return $value;
  }
  
  public function clear() {
    $_SESSION['cart66'] = null;
  }
  
  public function destroy() {
    unset($_SESSION['cart66']);
  }
  
  public function dump() {
    $out = "Cart66 Native Session Dump:\n\n";
    $out .= print_r($_SESSION['cart66']);
    echo $out;
  }

}