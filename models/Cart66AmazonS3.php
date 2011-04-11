<?php
/*
$file = 'headshot.jpg';
$bucket = 'cart66_downloads';
$link = Cart66AmazonS3::prepareS3Url($bucket, $file, '5 minutes');
echo $link;
*/
class Cart66AmazonS3 {
  
  public static function prepareS3Url($bucket, $file, $duration='5 minutes') {

    $awsKeyId = 'AKIAJNOCYELGZ2HVDRFA';                         // this is the non-secret key ID.
    $awsSecretKey = 'jMx4GDGdQiqAY2Rp6neQqhtB7RD8k1wOvoqb1ZtP'; // this is the SECRET access key!


    $file = rawurlencode($file); 
    $file = str_replace('%2F', '/', $file);
    $path = $bucket .'/'. $file;

    $expires = strtotime($duration);

    $stringToSign = self::getStringToSign('GET', $expires, "/$path"); 
    $signature = self::encodeSignature($stringToSign, $awsSecretKey); 

    $url = "http://$bucket.s3.amazonaws.com/$file";
    $url .= '?AWSAccessKeyId='.$awsKeyId
           .'&Expires='.$expires
           .'&Signature='.$signature;

    return $url;
  }
  
  protected static function getStringToSign($request_type, $expires, $uri) {
     return "$request_type\n\n\n$expires\n$uri";
  }

  protected static function encodeSignature($s, $key) {
      $s = utf8_encode($s);
      $s = hash_hmac('sha1', $s, $key, true);
      $s = base64_encode($s);
      return urlencode($s);
  }

}