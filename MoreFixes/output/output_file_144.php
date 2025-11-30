function formulaires_editer_liens_charger_dist($a, $b, $c, $options = []) {

	// compat avec ancienne signature ou le 4eme argument est $editable
	if (!is_array($options)) {
		$options = ['editable' => $options];
	} elseif (!isset($options['editable'])) {
		$options['editable'] = true;
	}

	$editable = $options['editable'];

	[$table_source, $objet, $id_objet, $objet_lien] = determine_source_lien_objet($a, $b, $c);
	if (!$table_source or !$objet or !$objet_lien or !$id_objet) {
		return false;
	}

	$objet_source = objet_type($table_source);
	$table_sql_source = table_objet_sql($objet_source);

	// verifier existence de la table xxx_liens
	include_spip('action/editer_liens');
	if (!objet_associable($objet_lien)) {
		return false;
	}

	// L'éditabilité :) est définie par un test permanent (par exemple "associermots") ET le 4ème argument
	include_spip('inc/autoriser');
	$editable = ($editable and autoriser('associer' . $table_source, $objet, $id_objet)
		and autoriser('modifier', $objet, $id_objet));

	if (
		!$editable and !count(objet_trouver_liens(
			[$objet_lien => '*'],
			[($objet_lien == $objet_source ? $objet : $objet_source) => $id_objet]
		))
	) {
		return false;
	}

	// squelettes de vue et de d'association
	// ils sont différents si des rôles sont définis.
	$skel_vue = $table_source . '_lies';
	$skel_ajout = $table_source . '_associer';

	// description des roles
	include_spip('inc/roles');
	if ($roles = roles_presents($objet_source, $objet)) {
		// on demande de nouveaux squelettes en conséquence
		$skel_vue = $table_source . '_roles_lies';
		$skel_ajout = $table_source . '_roles_associer';
	}

	$oups = '';
	if ($editable) {
		$oups = _request('_oups') ?? '';
		if ($oups) {
			if (json_decode(base64_decode($oups, true))) {
				// on est bon, rien a faire
			} else {
				$oups = '';
			}
		}
	}
	$valeurs = [
		'id' => "$table_source-$objet-$id_objet-$objet_lien", // identifiant unique pour les id du form
		'_vue_liee' => $skel_vue,
		'_vue_ajout' => $skel_ajout,
		'_objet_lien' => $objet_lien,
		'id_lien_ajoute' => _request('id_lien_ajoute'),
		'objet' => $objet,
		'id_objet' => $id_objet,
		'objet_source' => $objet_source,
		'table_source' => $table_source,
		'recherche' => '',
		'visible' => 0,
		'ajouter_lien' => '',
		'supprimer_lien' => '',
		'qualifier_lien' => '',
		'ordonner_lien' => '',
		'desordonner_liens' => '',
		'_roles' => $roles, # description des roles
		'_oups' => entites_html($oups),
		'editable' => $editable,
	];

	// les options non definies dans $valeurs sont passees telles quelles au formulaire html
	$valeurs = array_merge($options, $valeurs);

	return $valeurs;
}
