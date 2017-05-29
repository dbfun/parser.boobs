<?php

/**
 * @package Сore
 */

/**
 * Различные способы получения данных
 */

class ParseDataGetter {

  public function GetHeaders($url, $login = '', $password = '') {
    $contentPage = '';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, 1);
    curl_setopt($ch, CURLOPT_NOBODY, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    if($login && $password) curl_setopt($ch, CURLOPT_USERPWD, $login.':'.$password);
    // if (LOCAL === true) curl_setopt($ch, CURLOPT_PROXY, 'kaa:au0aiGhi@192.168.0.220:3128');
    $contentPage = curl_exec($ch);
    curl_close($ch);
    return trim($contentPage);
  }

  public function GetPageContent($url, $fh = '', $login = '', $password = '') {
    $contentPage = '';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    if($login && $password) curl_setopt($ch, CURLOPT_USERPWD, $login.':'.$password);
    // if (LOCAL === true) curl_setopt($ch, CURLOPT_PROXY, 'kaa:au0aiGhi@192.168.0.220:3128');
    if(is_resource($fh)) curl_setopt($ch, CURLOPT_FILE, $fh);
    $contentPage = curl_exec($ch);
    curl_close($ch);
    return $contentPage;
  }

  public function uploadImage($destPicName, $sourcePicUri, $login = '', $password = '') {
    // if (LOCAL === true) return $destPicName;
    if (file_exists($destPicName)) return $destPicName;
    $headersPic = self::GetHeaders($sourcePicUri, $login, $password);
    if (preg_match('/200 OK/i', $headersPic) && preg_match("/Content-Type:\s*(image\/.*)/i", $headersPic)) {
      $fh = fopen($destPicName, 'w');
      self::GetPageContent($sourcePicUri, $fh, $login, $password);
      fclose($fh);
      if (file_exists($destPicName) && filesize($destPicName) > 0) return $destPicName;
      unlink($destPicName);
      return null;
    }
    return null;
  }
}