<?php
class Cart66Session {
  
  /*
  public function __construct() {
    self::$_validRequest = true;
    self::_init();
  }
  */
  
  public static function setMaxLifetime($minutes) {
    
  }
  
  public static function touch() {
    if(Cart66Common::sessionType() == 'database') {
      Cart66SessionDb::touch();
    }
  }
  
  public static function set($key, $value, $forceSave=false) {
    if(Cart66Common::sessionType() == 'database') {
      Cart66SessionDb::set($key, $value, $forceSave);
    }
    else {
      Cart66SessionNative::set($key, $value, $forceSave);
    }
  }
  
  public static function drop($key, $forceSave=false) {
    if(Cart66Common::sessionType() == 'database') {
      Cart66SessionDb::drop($key, $forceSave);
    }
    else {
      Cart66SessionNative::drop($key, $forceSave);
    }
  }
  
  public static function get($key) {
    $value = false;
    if(Cart66Common::sessionType() == 'database') {
      $value = Cart66SessionDb::get($key);
    }
    else {
      $value = Cart66SessionNative::get($key);
    }
    return $value;
  }
  
  public function clear() {
    if(Cart66Common::sessionType() == 'database') {
      $value = Cart66SessionDb::clear();
    }
    else {
      $value = Cart66SessionNative::clear();
    }
  }
  
  public function destroy() {
    if(Cart66Common::sessionType() == 'database') {
      $value = Cart66SessionDb::destroy();
    }
    else {
      $value = Cart66SessionNative::destroy();
    }
  }
  
  public function dump() {
    if(Cart66Common::sessionType() == 'database') {
      $value = Cart66SessionDb::dump();
    }
    else {
      $value = Cart66SessionNative::dump();
    }
  }
  
}