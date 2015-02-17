<?php

  /* Version: 1.4.0 - now with more fours and a zero */
  // Modified and stripped down by Prakhar Bahuguna

  define('CACHE_TIME', 2419200); // default: 1 month

  $document_root   = $_SERVER['DOCUMENT_ROOT'];
  $requested_uri   = parse_url(urldecode($_SERVER['REQUEST_URI']), PHP_URL_PATH);
  $requested_file  = basename($requested_uri);
  $source_file     = $document_root.$requested_uri;
  $source_ext      = strtolower(pathinfo($source_file, PATHINFO_EXTENSION));
  $retina_file     = pathinfo($source_file, PATHINFO_DIRNAME).'/'.pathinfo($source_file, PATHINFO_FILENAME).'@2x.'.pathinfo($source_file, PATHINFO_EXTENSION);
  $cache_directive = 'no-transform';

  // Image was requested
  if (in_array($source_ext, array('png', 'gif', 'jpg', 'jpeg', 'bmp')))
  {

    // Check if a cookie is set
    $cookie_value = false;
    if (isset($_COOKIE['devicePixelRatio']))
    {
      $cookie_value = floatval($_COOKIE['devicePixelRatio']);
    }
    else
    {
      // Force revalidation of cache on next request
      $cache_directive = 'no-cache';
    }

    // Check if DPR is high enough to warrant retina image
    // Modified to allow forcing retina image with parameter retina=1
    if ($_GET["retina"] || ($cookie_value !== false && $cookie_value > 1))
    {
      // Check if retina image exists
      if (file_exists($retina_file))
      {
        $source_file = $retina_file;
      }
    }

    // Check if we can shrink a larger version of the image
    if (!file_exists($source_file) && ($source_file !== $retina_file))
    {
      // Check if retina image exists
      if (file_exists($retina_file))
      {
        $source_file = $retina_file;
      }
    }

    // Check if the image to send exists
    if (!file_exists($source_file))
    {
      header('HTTP/1.1 404 Not Found', true);
      exit();
    }


    // Send cache headers
    header("Cache-Control: private, {$cache_directive}, max-age=".CACHE_TIME, true);
    date_default_timezone_set('GMT');
    header('Expires: '.gmdate('D, d M Y H:i:s', time()+CACHE_TIME).' GMT', true);
    if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && (strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) === filemtime($source_file)))
    {
      // File in cache hasn't change
      header('Last-Modified: '.gmdate('D, d M Y H:i:s', filemtime($source_file)).' GMT', true, 304);
      exit();
    }
    // Added by Prakhar: Last-Modified header should be set for *all* requests.
    header('Last-Modified: '.gmdate('D, d M Y H:i:s', filemtime($source_file)).' GMT');

    // Send image headers
    if (in_array($source_ext, array('png', 'gif', 'jpeg', 'bmp')))
    {
      header("Content-Type: image/".$source_ext, true);
    }
    else
    {
      header("Content-Type: image/jpeg", true);
    }
    header('Content-Length: '.filesize($source_file), true);

    // Send file
    ob_flush();
    flush();
    readfile($source_file);
    exit();
  }

  // DPR value was sent
  elseif(isset($_GET['devicePixelRatio']))
  {
    $dpr = $_GET['devicePixelRatio'];

    // Validate value before setting cookie
    if (''.floatval($dpr) !== $dpr)
    {
      $dpr = '1';
    }

    setcookie('devicePixelRatio', $dpr);
    exit();
  }

  // Otherwise respond with an empty content
  header('HTTP/1.1 204 No Content', true);
?>
