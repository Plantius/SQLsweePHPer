function precharger_objet($type, $id_objet, $id_rubrique = 0, $lier_trad = 0, $champ_titre = 'titre') {

	$table = table_objet_sql($type);
	$_id_objet = id_table_objet($table);

	// si l'objet existe deja, on retourne simplement ses valeurs
	if (is_numeric($id_objet)) {
		return sql_fetsel("*", $table, "$_id_objet=$id_objet");
	}

	// ici, on demande une creation.
	// on prerempli certains elements : les champs si traduction,
	// les id_rubrique et id_secteur si l'objet a ces champs
	$desc = lister_tables_objets_sql($table);
	# il faudrait calculer $champ_titre ici
	$is_rubrique = isset($desc['field']['id_rubrique']);
	$is_secteur = isset($desc['field']['id_secteur']);

	// si demande de traduction
	// on recupere les valeurs de la traduction
	if ($lier_trad) {
		if ($select = charger_fonction("precharger_traduction_" . $type, 'inc', true)) {
			$row = $select($id_objet, $id_rubrique, $lier_trad);
		} else {
			$row = precharger_traduction_objet($type, $id_objet, $id_rubrique, $lier_trad, $champ_titre);
		}
	} else {
		$row[$champ_titre] = '';
		if ($is_rubrique) {
			$row['id_rubrique'] = $id_rubrique;
		}
	}

	// calcul de la rubrique
	# note : comment faire pour des traductions sur l'objet rubriques ?
	if ($is_rubrique) {
		// appel du script a la racine, faut choisir 
		// admin restreint ==> sa premiere rubrique
		// autre ==> la derniere rubrique cree
		if (!$row['id_rubrique']) {
			if ($GLOBALS['connect_id_rubrique']) {
				$row['id_rubrique'] = $id_rubrique = current($GLOBALS['connect_id_rubrique']);
			} else {
				$row_rub = sql_fetsel("id_rubrique", "spip_rubriques", "", "", "id_rubrique DESC", 1);
				$row['id_rubrique'] = $id_rubrique = $row_rub['id_rubrique'];
			}
			if (!autoriser('creerarticledans', 'rubrique', $row['id_rubrique'])) {
				// manque de chance, la rubrique n'est pas autorisee, on cherche un des secteurs autorises
				$res = sql_select("id_rubrique", "spip_rubriques", "id_parent=0");
				while (!autoriser('creerarticledans', 'rubrique', $row['id_rubrique']) && $row_rub = sql_fetch($res)) {
					$row['id_rubrique'] = $row_rub['id_rubrique'];
				}
			}
