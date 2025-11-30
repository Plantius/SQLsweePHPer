            array_push($checklist, $item['itemID']);
        }
        if ($this->ESI->getDEBUG()) inform(get_class(), "List of itemIDs: ". json_encode($checklist));
        // contact ESI
        inform(get_class(), 'Getting ' . count($checklist) . ' Asset names from ESI...');
        if (count($checklist) > 0) {
            $this->setRoute('/v1/corporations/' . $this->ESI->getCorporationID() . '/assets/names/');
            $this->setCacheInterval(0);
            if (count($checklist) > 0) {
                for ($i = 0; $i < count($checklist) / $MAX_IDS; $i++) { //fix for #85 - can only ask about 1000 names in one batch
                    if ($this->ESI->getDEBUG()) inform(get_class(), "Getting page $i");
                    $names = array_merge($names, $this->post('',json_encode(array_slice($checklist, $i * $MAX_IDS, $MAX_IDS))));
                }
            }
        }  else {
            return FALSE;
        }
        return $names;
    }
