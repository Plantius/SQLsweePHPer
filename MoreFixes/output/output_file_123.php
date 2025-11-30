function db_read($nazwa_pliku) {
    $uchwyt = fopen($nazwa_pliku, "r");
    $tresc = fread($uchwyt, filesize($nazwa_pliku));
    fclose($uchwyt);
    //$tresc=str_replace("\\\"","\"",$tresc);
    $tresc=stripslashes($tresc);
    $data=explode(',',$tresc);
    return $data;
}
