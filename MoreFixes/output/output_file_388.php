    public function getAreaDataCustom($area_object)
    {

        // Define Area
        $area = array();
        $area['name'] = $area_object->name;
        if (empty($area['name'])) {
            $area['name'] = 'N/A';
        }
        $area['id'] = $area_object->id;
        $area['coordinates'] = $area_object->coordinates;

        // Check for proper coordinates pattern
        if (preg_match('/^[0-9\s\(\)\,\.\-]+$/', (string) $area_object->coordinates)) {
            $fields = array();
            foreach ($area_object->column_fields as $field) {
                $fields[$field] = $area_object->$field;
            }
            // Define Maps Info Window HTML by Sugar Smarty Template
            $this->sugarSmarty->assign("module_type", 'jjwg_Areas');
            $this->sugarSmarty->assign("fields", $fields); // display fields array
            // Use @ error suppression to avoid issues with SugarCRM On-Demand
            $area['html'] = @$this->sugarSmarty->fetch('./custom/modules/jjwg_Areas/tpls/AreasInfoWindow.tpl');
            if (empty($area['html'])) {
                $area['html'] = $this->sugarSmarty->fetch('./modules/jjwg_Areas/tpls/AreasInfoWindow.tpl');
            }
            $area['html'] = preg_replace('/\n\r/', ' ', (string) $area['html']);
            //var_dump($marker['html']);
            return $area;
        } else {
            return false;
        }
    }
