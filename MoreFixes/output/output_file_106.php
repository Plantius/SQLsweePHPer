function critere_where_dist($idb, &$boucles, $crit) {
	$boucle = &$boucles[$idb];
	if (isset($crit->param[0])) {
		$_where = calculer_liste($crit->param[0], $idb, $boucles, $boucle->id_parent);
	} else {
		$_where = '@$Pile[0]["where"]';
	}

	if ($crit->cond) {
		$_where = "(($_where) ? ($_where) : '')";
	}

	if ($crit->not) {
		$_where = "array('NOT',$_where)";
	}

	$boucle->where[] = $_where;
}
