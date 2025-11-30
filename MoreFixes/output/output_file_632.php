				var_dump($matches);
				$ok=false;
				break;
			}
			//print __METHOD__." Result for checking we don't have non escaped string in sql requests for file ".$file."\n";
			$this->assertTrue($ok, 'Found non quoted or not casted var into sql request '.$file['relativename'].' - Bad.');
			//exit;


			// Check string   ='".$this->xxx   with xxx that is not 'escape'. It means we forget a db->escape when forging sql request.
			preg_match_all('/=\s*\'"\s*\.\s*\$this->(....)/', $filecontent, $matches, PREG_SET_ORDER);
			foreach ($matches as $key => $val) {
				if ($val[1] != 'db->' && $val[1] != 'esca') {
					$ok=false;
					break;
				}
				//if ($reg[0] != 'db') $ok=false;
			}
			//print __METHOD__." Result for checking we don't have non escaped string in sql requests for file ".$file."\n";
			$this->assertTrue($ok, 'Found non escaped string in building of a sql request '.$file['relativename'].' - Bad.');
			//exit;

			// Check string sql|set...'".$yyy->xxx   with xxx that is not 'escape', 'idate', .... It means we forget a db->escape when forging sql request.
			preg_match_all('/(sql|SET).+\s*\'"\s*\.\s*\$(.........)/', $filecontent, $matches, PREG_SET_ORDER);
			foreach ($matches as $key => $val) {
				if (! in_array($val[2], array('this->db-', 'this->esc', 'db->escap', 'dbsession', 'db->idate', 'excludeGr', 'includeGr'))) {
					$ok=false;
					break;
				}
				//if ($reg[0] != 'db') $ok=false;
			}
			//print __METHOD__." Result for checking we don't have non escaped string in sql requests for file ".$file."\n";
			$this->assertTrue($ok, 'Found non escaped string in building of a sql request '.$file['relativename'].': '.$val[0].' - Bad.');
			//exit;

			// Check string 'IN (".xxx' or 'IN (\'.xxx'  with xxx that is not '$this->db->sanitize' and not '$db->sanitize'. It means we forget a db->sanitize when forging sql request.
			preg_match_all('/ IN \([\'"]\s*\.\s*(.........)/i', $filecontent, $matches, PREG_SET_ORDER);
			foreach ($matches as $key => $val) {
				if (!in_array($val[1], array('$db->sani', '$this->db', 'getEntity', 'WON\',\'L', 'self::STA', 'Commande:', 'CommandeF', 'Entrepot:', 'Facture::', 'FactureFo', 'ExpenseRe', 'Societe::', 'Ticket::S'))) {
					$ok=false;
					break;
				}
				//if ($reg[0] != 'db') $ok=false;
			}
			//print __METHOD__." Result for checking we don't have non escaped string in sql requests for file ".$file."\n";
			$this->assertTrue($ok, 'Found non sanitized string in building of a IN or NOT IN sql request '.$file['relativename'].' - Bad.');
			//exit;

			// Check string 'IN (\'".xxx'   with xxx that is not '$this->db->sanitize' and not '$db->sanitize'. It means we forget a db->sanitize when forging sql request.
			preg_match_all('/ IN \(\'"\s*\.\s*(.........)/i', $filecontent, $matches, PREG_SET_ORDER);
			foreach ($matches as $key => $val) {
				if (!in_array($val[1], array('$db->sani', '$this->db', 'getEntity', 'WON\',\'L', 'self::STA', 'Commande:', 'CommandeF', 'Entrepot:', 'Facture::', 'FactureFo', 'ExpenseRe', 'Societe::', 'Ticket::S'))) {
					$ok=false;
					break;
				}
				//if ($reg[0] != 'db') $ok=false;
			}
			//print __METHOD__." Result for checking we don't have non escaped string in sql requests for file ".$file."\n";
			$this->assertTrue($ok, 'Found non sanitized string in building of a IN or NOT IN sql request '.$file['relativename'].' - Bad.');
			//exit;

			// Test that output of $_SERVER\[\'QUERY_STRING\'\] is escaped.
			$ok=true;
			$matches=array();
			preg_match_all('/(..............)\$_SERVER\[\'QUERY_STRING\'\]/', $filecontent, $matches, PREG_SET_ORDER);
			foreach ($matches as $key => $val) {
				if ($val[1] != 'scape_htmltag(' && $val[1] != 'ing_nohtmltag(' && $val[1] != 'dol_escape_js(') {
					$ok=false;
					break;
				}
			}
			$this->assertTrue($ok, 'Found a $_SERVER[\'QUERY_STRING\'] without dol_escape_htmltag neither dol_string_nohtmltag around it, in file '.$file['relativename'].' ('.$val[1].'$_SERVER[\'QUERY_STRING\']). Bad.');


			// Test that first param of print_liste_field_titre is a translation key and not the translated value
			$ok=true;
			$matches=array();
			// Check string ='print_liste_field_titre\(\$langs'.
			preg_match_all('/print_liste_field_titre\(\$langs/', $filecontent, $matches, PREG_SET_ORDER);
			foreach ($matches as $key => $val) {
				   $ok=false;
				   break;
			}
			$this->assertTrue($ok, 'Found a use of print_liste_field_titre with first parameter that is a translated value instead of just the translation key in file '.$file['relativename'].'. Bad.');


			// Test we don't have <br />
			$ok=true;
			$matches=array();
			preg_match_all('/<br\s+\/>/', $filecontent, $matches, PREG_SET_ORDER);
			foreach ($matches as $key => $val) {
				if ($file['name'] != 'functions.lib.php') {
					$ok=false;
					break;
				}
			}
			$this->assertTrue($ok, 'Found a tag <br /> that is for xml in file '.$file['relativename'].'. You must use html syntax <br> instead.');


			// Test we don't have name="token" value="'.$_SESSION['newtoken'], we must use name="token" value="'.newToken() instead.
			$ok=true;
			$matches=array();
			preg_match_all('/name="token" value="\'\s*\.\s*\$_SESSION/', $filecontent, $matches, PREG_SET_ORDER);
			foreach ($matches as $key => $val) {
				if ($file['name'] != 'excludefile.php') {
					$ok=false;
					break;
				}
			}
			$this->assertTrue($ok, 'Found a forbidden string sequence into '.$file['relativename'].' : name="token" value="\'.$_SESSION[..., you must use a newToken() instead of $_SESSION[\'newtoken\'].');


			// Test we don't have @var array(
			$ok=true;
			$matches=array();
			preg_match_all('/@var\s+array\(/', $filecontent, $matches, PREG_SET_ORDER);
			foreach ($matches as $key => $val) {
				$ok=false;
				break;
			}
			$this->assertTrue($ok, 'Found a declaration @var array() instead of @var array in file '.$file['relativename'].'.');


			// Test we don't have CURDATE()
			$ok=true;
			$matches=array();
			preg_match_all('/CURDATE\(\)/', $filecontent, $matches, PREG_SET_ORDER);
			foreach ($matches as $key => $val) {
				$ok=false;
				break;
			}
			$this->assertTrue($ok, 'Found a CURDATE\(\) into code. Do not use this SQL method in file '.$file['relativename'].'. You must use the PHP function dol_now() instead.');
		}
