	function prorate($toFile, $toW, $toH){
		if(!$this->im){
			return false;
		}
		$toWH = $toW / $toH;
		$srcWH = $this->srcW / $this->srcH;
		if ($toWH<=$srcWH) {
			$ftoW = $toW;
			$ftoH = $ftoW * ($this->srcH / $this->srcW);
		} else {
			$ftoH = $toH;
			$ftoW = $ftoH * ($this->srcW / $this->srcH);
		} 
		if ($this->srcW > $toW || $this->srcH > $toH) {
			$cImg = $this->creatImage($this->im, $ftoW, $ftoH, 0, 0, 0, 0, $this->srcW, $this->srcH);
			return $this->echoImage($cImg, $toFile);
		} else {
			$cImg = $this->creatImage($this->im, $this->srcW, $this->srcH, 0, 0, 0, 0, $this->srcW, $this->srcH);
			return $this->echoImage($cImg, $toFile);
		} 
	} 
