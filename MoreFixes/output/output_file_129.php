function db_write2($nazwa_pliku,$data) {
include('../config/config.php');
  if ($LM_READONLY==0) {
    $uchwyt = fopen($nazwa_pliku, "w");
    if (count($data)>0) {
		$tresc=implode('|',$data);
    }
    fwrite($uchwyt, $tresc);
    fclose($uchwyt);
  }
}
