function getUrl(){
  $a=parse_url(sprintf(
    "%s://%s%s",
    isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',
    $_SERVER['SERVER_NAME'],
    $_SERVER['REQUEST_URI']
  ));
  if (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT']!=80 && $_SERVER['SERVER_PORT']!=443) {
      $port=":${_SERVER['SERVER_PORT']}"; 
  } else {
      $port='';
  }
  $path=preg_split('/[\w]+\.php/',$a['path']);
  return $a['scheme'].'://'.$a['host'].$port.$path[0];
}
