<?php
class Cart66SessionDb {
  
  protected static $_started;
  protected static $_maxLifetime;
  protected static $_userData;
  protected static $_data;
  protected static $_validRequest;
  
  public function __construct() {
    self::$_validRequest = true;
    self::_init();
  }
  
  public static function setMaxLifetime($minutes) {
    if(is_numeric($minutes)) {
      self::$_maxLifetime = $minutes;
    }
    else {
      self::$_maxLifetime = 30;
    }
  }
  
  public static function touch() {
    if(self::$_data['id'] > 0) {
      // Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Touching the session: " . self::$_data['session_id']);
      self::_save();
    }
  }
  
  public static function set($key, $value, $forceSave=false) {
    self::_init();
    self::$_userData[$key] = $value;
    if($forceSave) { self::_save(); }
  }
  
  public static function drop($key, $forceSave=false) {
    self::_init();
    if(isset(self::$_userData[$key])) {
      unset(self::$_userData[$key]);
      if($forceSave) { self::_save(); }
    }
  }
  
  public static function get($key) {
    self::_init();
    return isset(self::$_userData[$key]) ? self::$_userData[$key] : false;
  }
  
  public static function clear() {
    self::$_userData = array();
  }
  
  public static function destroy() {
    self::_deleteMe();
    self::_newSession();
  }
  
  public static function dump() {
    $out = "Cart66 Session Dump:\n\n";
    $out .= print_r(self::$_userData, true);
    $out .= print_r(self::$_data, true);
    echo $out;
  }
  
  protected static function _init() {
    if(!self::$_started) {
      self::$_started = true;
      self::$_data = array(
        'id' => null,
        'session_id' => 0,
        'ip_address' => '',
        'user_agent' => '',
        'last_activity' => '',
        'user_data' => ''
      );
      self::$_userData = array();
      self::setMaxLifetime(30);
      self::_start();
    }
  }
  
  protected function _start() {
    self::$_validRequest = true;
    
    // Do not start sessions for requests to these file extensions
    $url = array_shift(explode('?', $_SERVER['REQUEST_URI']));
    if(strpos(basename($url), '.')) {
      $ext = strtolower(end(explode('.', basename($url))));
      $ignoreList = array('png', 'jpg', 'jpeg', 'css', 'gif', 'js', 'txt', 'mp3', 'wma', 'swf', 'fla', 'zip');
      if(in_array($ext, $ignoreList)) {
        self::$_validRequest = false;
        Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Not starting session for this request: $url");
      }
    }
    
    if(self::$_validRequest) {
      $sid = isset($_COOKIE['Cart66SID']) ? $_COOKIE['Cart66SID'] : false;
      /*
      Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] ***************************************************\n" .
        "Starting session with Cart66SID: $sid\nREQUEST: " . $_SERVER['REQUEST_URI'] . "\nQUERY STRING: " . $_SERVER['QUERY_STRING']);
      */
      self::_loadSession($sid);
    }
    
  }
  
  
  /**
   * Load the session from the database with the given session id or create a new session
   * if no active session is available.
   * 
   * @param string $sessionId The 40 character session id for the session to load
   * @return void
   */
  protected static function _loadSession($sessionId=false) {
    global $wpdb;
    $tableName = Cart66Common::getTableName('sessions');
    $loaded = false;
    
    if($sessionId) {
      $sql = "select * from $tableName where session_id = %s order by id desc";
      $sql = $wpdb->prepare($sql, $sessionId);
      $data = $wpdb->get_row($sql, ARRAY_A);
      if($data && self::_isValid($data)) {
        self::$_userData = unserialize($data['user_data']);
        unset($data['user_data']);
        self::$_data = $data;
        $loaded = true;
      }
    }
    else {
      $sessionId = "No Session Id Provided";
    }
    
    if(!$loaded) {
      Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] _loadSession() was unable to load the session id: $sessionId");
      self::_newSession();
    }
    else {
      // Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Successfully loaded session: $sessionId");
    }
    
  }
  
  
  /**
   * Create a new session row in the database and return the new session id
   * 
   * @return string The new session id
   */
  protected static function _newSession() {
    Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Creating a new session");
    $userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'noUserAgent';
    $data = array(
      'id' => null,
      'session_id' => self::_newSessionId(),
      'ip_address' => self::_getIp(),
      'user_agent' => $userAgent,
      'last_activity' => date('Y-m-d H:i:s', Cart66Common::localTs()),
      'user_data' => serialize(self::$_userData)
    );
    self::$_data = $data;
    self::_save();
    
    header('P3P:CP="IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT"');
    setcookie("Cart66SID",self::$_data['session_id'] , false, "/", false);
    
    self::_deleteExpiredSessions();
    return $data['session_id'];
  }
  
  
  /**
   * Return true if the last_activity date is older than $_maxLifetime minutes in the past
   * 
   * @return boolean True if session is active, otherwise false
   */
  public static function _isActive($data=null) {
    if(!is_array($data)) { $data = self::$_data; }
    $isActive = false;
    if($data['id'] > 0) {
      $expireTs = strtotime('-' . self::$_maxLifetime . ' minutes', Cart66Common::localTs());
      $lastActiveTs = strtotime($data['last_activity'], Cart66Common::localTs());
      // Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] $lastActiveTs > $expireTs Last activity: " . $data['last_activity']);
      $isActive = $lastActiveTs > $expireTs;
    }
    return $isActive;
  }
  
  
  /**
   * Check the the attributes of the session to make sure the requester is the owner of the session
   * 
   * @return boolean
   */
  protected static function _isValid($data) {
    $isValid = true;
    
    if($data['ip_address'] != self::_getIp()) {
      $isValid = false;
      Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Session is not valid - IP Address changed");
    }
    elseif($data['user_agent'] != $_SERVER['HTTP_USER_AGENT']) {
      $isValid = false;
      Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Session is not valid - User agent changed");
    }
    elseif(!self::_isActive($data)) {
      $isValid = false;
      Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Session is not valid - Expired");
    }
    
    return $isValid;
  }

  
  /**
   * Return a 40 character random string that is not already in the database
   * 
   * @return string
   */
  protected function _newSessionId() {
    global $wpdb;
    
    do {
      $sessionId = Cart66Common::getRandString(40);
      $tableName = Cart66Common::getTableName('sessions');
      $sql = "select count(*) from $tableName where session_id = %s";
      $sql = $wpdb->prepare($sql, $sessionId);
      $count = $wpdb->get_var($sql);
    }
    while($count > 0);
    
    return $sessionId;
  }
    
	
	/**
	 * Return the domain name with a leading dot.
	 * For example if the domain is www.example.com then return .example.com
	 * Likewise, if the domain is example.com then return .example.com
	 * The returned domain is used to set the domain for the cookie availability.
	 * 
	 * @return string
	 */
	protected function _getDomain() {
	  $url = parse_url( strtolower( get_bloginfo('wpurl') ) );
    $domain = $url['host'];
    return $domain;
	}
	
	
	protected function _deleteExpiredSessions() {
	  global $wpdb;
	  $tableName = Cart66Common::getTableName('sessions');
	  $cutOffDate = date('Y-m-d H:i:s', strtotime('-' . self::$_maxLifetime . ' minutes', Cart66Common::localTs()));
	  $sql = "DELETE from $tableName where last_activity < %s";
	  $sql = $wpdb->prepare($sql, $cutOffDate);
	  $wpdb->query($sql);
	}
	
	
	/**
   * Save the session data to the database.
   * Set the last activity date and serialize the user data before 
   */
	protected static function _save() {
	  if(self::$_validRequest) {
	    self::$_data['user_data'] = serialize(self::$_userData);
  	  self::$_data['last_activity'] = date('Y-m-d H:i:s', Cart66Common::localTs());
  	  //Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Saving Session User Data: " . print_r(self::$_userData, true));
      self::$_data['id'] > 0 ? self::_update() : self::_insert();
	  }
	  else {
	    $reqInfo = "\nCart66SID: $sid\nREQUEST: " . $_SERVER['REQUEST_URI'] . "\nQUERY STRING: " . $_SERVER['QUERY_STRING'];
	    Cart66Common::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Not saving session because the request is being ignored $reqInfo");
	  }
	}
	
	
	protected static function _insert() {
	  global $wpdb;
	  $wpdb->insert(Cart66Common::getTableName('sessions'), self::$_data);
	  self::$_data['id'] = $wpdb->insert_id;
	}
	
	
	protected static function _update() {
	  global $wpdb;
	  $wpdb->update(Cart66Common::getTableName('sessions'), self::$_data, array('id' => self::$_data['id']));
	}
	
	
	protected static function _deleteMe() {
	  global $wpdb;
	  $tableName = Cart66Common::getTableName('sessions');
	  $sql = "DELETE from $tableName where id = %d";
	  $sql = $wpdb->prepare($sql, self::$_data['id']);
	  $wpdb->query($sql);
	}
	
	protected static function _getIp(){
      $ip = (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) ? $_SERVER["HTTP_X_FORWARDED_FOR"] : false;
      if (!$ip){
        $ip = $_SERVER["REMOTE_ADDR"];
      }          
      $ipArray = explode(",", $ip); 
      return $ipArray[0];
  }

}