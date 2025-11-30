function file_put_out($file,$download=-1,$downFilename=false){
	$error = false;
	if (!file_exists($file)){
		$error = 'file not exists';
	}else if (!path_readable($file)){
		$error = 'file not readable';
	}else if (!$fp = @fopen($file, "rb")){
		$error = 'file open error!';
	} 
	if($error !== false){
		if($downFilename === false){
			return;
		}else{
			show_json($error,false);
		}
	}

	$start= 0;
	$file_size = get_filesize($file);
	$end  = $file_size - 1;
	@ob_end_clean();
	@set_time_limit(0);

	$time = gmdate('D, d M Y H:i:s',filemtime($file));
	$filename = get_path_this($file);
	if($downFilename !== false){
		$filename = $downFilename;
	}

	$mime = get_file_mime(get_path_ext($filename));
	if ($download === -1 && !mime_support($mime)){
		$download = true;
	}
	$headerName = rawurlencode(iconv_app($filename));
	$headerName = '"'.$headerName."\"; filename*=utf-8''".$headerName;
	if ($download) {
		header('Content-Type: application/octet-stream');
		header('Content-Transfer-Encoding: binary');
		header('Content-Disposition: attachment;filename='.$headerName);
	}else{
		header('Content-Type: '.$mime);
		header('Content-Disposition: inline;filename='.$headerName);
		if(strstr($mime,'text/')){
			//$charset = get_charset(file_get_contents($file));
			header('Content-Type: '.$mime.'; charset=');//避免自动追加utf8导致gbk网页乱码
		}
	}
	
	//缓存文件
	header('Expires: '.gmdate('D, d M Y H:i:s',time()+3600*24*20).' GMT');
	header('Cache-Pragma: public');
	header('Pragma: public'); 
	header('Cache-Control: cache, must-revalidate');
	if (isset($_SERVER['If-Modified-Since']) && 
		(strtotime($_SERVER['If-Modified-Since']) == filemtime($file))) {
		header('304 Not Modified', true, 304);
		exit;
	}
	$etag = '"'.md5($time.$file_size).'"';
	if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && $_SERVER['HTTP_IF_NONE_MATCH'] == $etag){
		header("Etag: ".$etag, true, 304);
		exit;
	}
	header('Etag: '.$etag);
	header('Last-Modified: '.$time.' GMT');
	header("X-OutFileName: ".$filename);
	header("X-Powered-By: kodExplorer.");
	header("X-FileSize: ".$file_size);

	// 过滤svg中非法script内容; 避免xxs;
	if(!$download && get_path_ext($filename) == 'svg'){
		if($file_size > 1024*1024*5) {exit;}
		$content = file_get_contents($file);
		$content = removeXXS($content);
		echo $content;exit;
	}
	
	//远程路径不支持断点续传；打开zip内部文件
	if(!file_exists($file)){
		header('HTTP/1.1 200 OK');
		header('Content-Length: '.($end+1));
		return;
	}
