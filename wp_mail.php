<?php

if(!function_exists('wp_mail')) {
  /**
   * Send mail, similar to PHP's mail
   *
   * A true return value does not automatically mean that the user received the
   * email successfully. It just only means that the method used was able to
   * process the request without any errors.
   *
   * Using the two 'wp_mail_from' and 'wp_mail_from_name' hooks allow from
   * creating a from address like 'Name <email@address.com>' when both are set. If
   * just 'wp_mail_from' is set, then just the email address will be used with no
   * name.
   *
   * The default content type is 'text/plain' which does not allow using HTML.
   * However, you can set the content type of the email by using the
   * 'wp_mail_content_type' filter.
   *
   * If $message is an array, the key of each is used to add as an attachment
   * with the value used as the body. The 'text/plain' element is used as the
   * text version of the body, with the 'text/html' element used as the HTML
   * version of the body. All other types are added as attachments.
   *
   * The default charset is based on the charset used on the blog. The charset can
   * be set using the 'wp_mail_charset' filter.
   *
   * @since 1.2.1
   * @uses apply_filters() Calls 'wp_mail' hook on an array of all of the parameters.
   * @uses apply_filters() Calls 'wp_mail_from' hook to get the from email address.
   * @uses apply_filters() Calls 'wp_mail_from_name' hook to get the from address name.
   * @uses apply_filters() Calls 'wp_mail_content_type' hook to get the email content type.
   * @uses apply_filters() Calls 'wp_mail_charset' hook to get the email charset
   * @uses do_action_ref_array() Calls 'phpmailer_init' hook on the reference to
   *    phpmailer object.
   * @uses PHPMailer
   * @
   *
   * @param string|array $to Array or comma-separated list of email addresses to send message.
   * @param string $subject Email subject
   * @param string|array $message Message contents
   * @param string|array $headers Optional. Additional headers.
   * @param string|array $attachments Optional. Files to attach.
   * @return bool Whether the email contents were sent successfully.
   */
  
  function wp_mail($to, $subject, $message, $headers = '', $attachments = array()) {
    // Compact the input, apply the filters, and extract them back out
    extract(apply_filters('wp_mail', compact('to', 'subject', 'message', 'headers', 'attachments')));

    if(!is_array($attachments)) {
      $attachments = explode("\n", str_replace("\r\n", "\n", $attachments));
    }
  
    global $phpmailer;

    // (Re)create it, if it's gone missing
    if(!is_object($phpmailer) || !is_a($phpmailer, 'PHPMailer')) {
      require_once ABSPATH . WPINC . '/class-phpmailer.php';
      require_once ABSPATH . WPINC . '/class-smtp.php';
      $phpmailer = new PHPMailer(true);
    }

    // Headers
    if(empty($headers)) {
      $headers = array();
    }
    else {
      if(!is_array($headers)) {
        // Explode the headers out, so this function can take both
        // string headers and an array of headers.
        $tempheaders = explode("\n", str_replace("\r\n", "\n", $headers));
      }
      else {
        $tempheaders = $headers;
      }
      $headers = array();
      $cc = array();
      $bcc = array();

      // If it's actually got contents
      if(!empty($tempheaders)) {
        // Iterate through the raw headers
        foreach((array) $tempheaders as $header) {
          if(strpos($header, ':') === false) {
            if(false !== stripos($header, 'boundary=')) {
              $parts = preg_split('/boundary=/i', trim($header));
              $boundary = trim(str_replace(array("'", '"'), '', $parts[1]));
            }
            continue;
          }
          // Explode them out
          list($name, $content) = explode(':', trim($header), 2);

          // Cleanup crew
          $name    = trim($name);
          $content = trim($content);

          switch(strtolower($name)) {
            // Mainly for legacy -- process a From: header if it's there
            case 'from':
              if(strpos($content, '<') !== false) {
                // So... making my life hard again?
                $from_name = substr($content, 0, strpos($content, '<') - 1);
                $from_name = str_replace('"', '', $from_name);
                $from_name = trim($from_name);

                $from_email = substr($content, strpos($content, '<') + 1);
                $from_email = str_replace('>', '', $from_email);
                $from_email = trim($from_email);
              }
              else {
                $from_email = trim($content);
              }
              break;
            case 'content-type':
              if(is_array($message)) {
                // Multipart email, ignore the content-type header
                break;
              }
              if(strpos($content, ';') !== false) {
                list($type, $charset) = explode(';', $content);
                $content_type = trim($type);
                if(false !== stripos($charset, 'charset=')) {
                  $charset = trim(str_replace(array('charset=', '"'), '', $charset));
                }
                elseif(false !== stripos($charset, 'boundary=')) {
                  $boundary = trim(str_replace(array('BOUNDARY=', 'boundary=', '"'), '', $charset));
                  $charset = '';
                }
              }
              else {
                $content_type = trim($content);
              }
              break;
            case 'cc':
              $cc = array_merge((array) $cc, explode(',', $content));
              break;
            case 'bcc':
              $bcc = array_merge((array) $bcc, explode(',', $content));
              break;
            default:
              // Add it to our grand headers array
              $headers[trim($name)] = trim($content);
              break;
          }
        }
      }
    }

    // Empty out the values that may be set
    $phpmailer->ClearAddresses();
    $phpmailer->ClearAllRecipients();
    $phpmailer->ClearAttachments();
    $phpmailer->ClearBCCs();
    $phpmailer->ClearCCs();
    $phpmailer->ClearCustomHeaders();
    $phpmailer->ClearReplyTos();

    // From email and name
    // If we don't have a name from the input headers
    if(!isset($from_name))
      $from_name = 'WordPress';

    /* If we don't have an email from the input headers default to wordpress@$sitename
     * Some hosts will block outgoing mail from this address if it doesn't exist but
     * there's no easy alternative. Defaulting to admin_email might appear to be another
     * option but some hosts may refuse to relay mail from an unknown domain. See
     * http://trac.wordpress.org/ticket/5007.
     */

    if(!isset($from_email)) {
      // Get the site domain and get rid of www.
      $sitename = strtolower($_SERVER['SERVER_NAME']);
      if(substr($sitename, 0, 4) == 'www.') {
        $sitename = substr($sitename, 4);
      }

      $from_email = 'wordpress@' . $sitename;
    }

    // Plugin authors can override the potentially troublesome default
    $phpmailer->From = apply_filters('wp_mail_from', $from_email);
    $phpmailer->FromName = apply_filters('wp_mail_from_name', $from_name);

    // Set destination addresses
    if(!is_array($to)) {
      $to = explode(',', $to);
    }
  
    foreach((array) $to as $recipient) {
      try {
        // Break $recipient into name and address parts if in the format "Foo <bar@baz.com>"
        $recipient_name = '';
        if(preg_match('/(.+)\s?<(.+)>/', $recipient, $matches)) {
          if(count($matches) == 3) {
            $recipient_name = $matches[1];
            $recipient = $matches[2];
          }
        }
        $phpmailer->AddAddress(trim($recipient), $recipient_name);
      }
      catch(phpmailerException $e) {
        continue;
      }
    }

    // If we don't have a charset from the input headers
    if(!isset($charset)) {
      $charset = get_bloginfo('charset');
    }
  
    // Set the content-type and charset
    $phpmailer->CharSet = apply_filters('wp_mail_charset', $charset);

    // Set mail's subject and body
    $phpmailer->Subject = $subject;

    if(is_string($message)) {
      $phpmailer->Body    = $message;

      // Set Content-Type and charset
      // If we don't have a content-type from the input headers
      if(!isset($content_type)) {
        $content_type = 'text/plain';
      }
    
      $content_type = apply_filters('wp_mail_content_type', $content_type);

      $phpmailer->ContentType = $content_type;

      // Set whether it's plaintext, depending on $content_type
      if('text/html' == $content_type) {
        $phpmailer->IsHTML(true);
      }
    
      // For backwards compatibility, new multipart emails should use
      // the array style $message. This never really worked well anyway
      if(false !== stripos($content_type, 'multipart') && ! empty($boundary)) {
        $phpmailer->AddCustomHeader(sprintf("Content-Type: %s;\n\t boundary=\"%s\"", $content_type, $boundary));
      }
    }
    elseif(is_array($message)) {
      foreach($message as $type => $bodies) {
        foreach((array) $bodies as $body) {
          if($type === 'text/html') {
            $phpmailer->Body = $body;
          }
          elseif($type === 'text/plain') {
            $phpmailer->AltBody = $body;
          }
          else {
            $phpmailer->AddAttachment($body, '', 'base64', $type);
          }
        }
      }
    }

    // Add any CC and BCC recipients
    if(!empty($cc)) {
      foreach((array) $cc as $recipient) {
        try {
          // Break $recipient into name and address parts if in the format "Foo <bar@baz.com>"
          $recipient_name = '';
          if(preg_match('/(.+)\s?<(.+)>/', $recipient, $matches)) {
            if(count($matches) == 3) {
              $recipient_name = $matches[1];
              $recipient = $matches[2];
            }
          }
          $phpmailer->AddCc(trim($recipient), $recipient_name);
        }
        catch(phpmailerException $e) {
          continue;
        }
      }
    }

    if(!empty($bcc)) {
      foreach((array) $bcc as $recipient) {
        try {
          // Break $recipient into name and address parts if in the format "Foo <bar@baz.com>"
          $recipient_name = '';
          if(preg_match('/(.+)\s?<(.+)>/', $recipient, $matches)) {
            if(count($matches) == 3) {
              $recipient_name = $matches[1];
              $recipient = $matches[2];
            }
          }
          $phpmailer->AddBcc(trim($recipient), $recipient_name);
        }
        catch(phpmailerException $e) {
          continue;
        }
      }
    }

    // Set to use PHP's mail()
    $phpmailer->IsMail();

    // Set custom headers
    if(!empty($headers)) {
      foreach((array) $headers as $name => $content) {
        $phpmailer->AddCustomHeader(sprintf('%1$s: %2$s', $name, $content));
      }
    }

    if(!empty($attachments)) {
      foreach($attachments as $attachment) {
        try {
          $phpmailer->AddAttachment($attachment);
        }
        catch(phpmailerException $e) {
          continue;
        }
      }
    }

    do_action_ref_array('phpmailer_init', array(&$phpmailer));

    // Send!
    try {
      $phpmailer->Send();
    }
    catch(phpmailerException $e) {
      return false;
    }

    return true;
  }

}