    public function getMarkerDataCustom($marker_object)
    {

        // Define Marker
        $marker = array();
        $marker['name'] = $marker_object->name;
        if (empty($marker['name'])) {
            $marker['name'] = 'N/A';
        }
        $marker['id'] = $marker_object->id;
        $marker['lat'] = $marker_object->jjwg_maps_lat;
        if (!$this->is_valid_lat($marker['lat'])) {
            $marker['lat'] = '0';
        }
        $marker['lng'] = $marker_object->jjwg_maps_lng;
        if (!$this->is_valid_lng($marker['lng'])) {
            $marker['lng'] = '0';
        }
        $marker['image'] = $marker_object->marker_image;
        if (empty($marker['image'])) {
            $marker['image'] = 'None';
        }

        if ($marker['lat'] != '0' || $marker['lng'] != '0') {
            $fields = array();
            foreach ($marker_object->column_fields as $field) {
                $fields[$field] = $marker_object->$field;
            }
            // Define Maps Info Window HTML by Sugar Smarty Template
            $this->sugarSmarty->assign("module_type", 'jjwg_Markers');
            $this->sugarSmarty->assign("fields", $fields); // display fields array
            // Use @ error suppression to avoid issues with SugarCRM On-Demand
            $marker['html'] = @$this->sugarSmarty->fetch('./custom/modules/jjwg_Markers/tpls/MarkersInfoWindow.tpl');
            if (empty($marker['html'])) {
                $marker['html'] = $this->sugarSmarty->fetch('./modules/jjwg_Markers/tpls/MarkersInfoWindow.tpl');
            }
            $marker['html'] = preg_replace('/\n\r/', ' ', (string) $marker['html']);
            //var_dump($marker['html']);
            return $marker;
        } else {
            return false;
        }
    }
