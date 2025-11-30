	public function print_section_subnets_table($User, $sectionId, $showSupernetOnly = false) {
		$html = array();

		# set custom fields
		$Tools = new Tools ($this->Database);
		$custom = $Tools->fetch_custom_fields ("subnets");

		# set hidden fields
		$hidden_fields = json_decode($User->settings->hiddenCustomFields, true);
		$hidden_fields = is_array($hidden_fields['subnets']) ? $hidden_fields['subnets'] : array();

		# check permission
		$permission = $this->check_permission($User->user, $sectionId);

		$showSupernetOnly = $showSupernetOnly ? '1' : '0';

		# permitted
		if ($permission != 0) {
			// add
			if ($permission>1) {
				$html[] = "<div class='btn-group'>";
				$html[] = '<button class="btn btn-sm btn-default btn-success editSubnet" data-action="add" data-sectionid="'.$sectionId.'" data-subnetId="" rel="tooltip" data-placement="left" title="'._('Add new subnet to section').'"><i class="fa fa-plus"></i> '._('Add subnet').'</button>';
				$html[] = "<button class='btn btn-sm btn-default btn-success open_popup' data-script='app/admin/subnets/find_free_section_subnets.php'  data-class='700' rel='tooltip' data-container='body'  data-placement='top' title='"._('Search for free subnets in section ')."'  data-sectionId='$sectionId'><i class='fa fa-sm fa-search'></i> "._("Find subnet")."</button>";
				$html[] = "</div>";
			}

			$html[] = '<table id="manageSubnets" class="table sorted-new table-striped table-condensed table-top table-no-bordered" data-pagination="true" data-cookie-id-table="sectionSubnets"  data-side-pagination="server" data-search="true" data-toggle="table" data-url="app/json/section/subnets.php?sectionId='.$sectionId.'&showSupernetOnly='.$showSupernetOnly.'">';
			$html[] = '<thead><tr>';

			$html[] = '<th data-field="subnet">'._('Subnet').'</th>';
			$html[] = '<th data-field="description">'._('Description').'</th>';
			if($User->get_module_permissions ("vlan")>0)
			$html[] = '<th data-field="vlan">'._('VLAN').'</th>';
			if($User->settings->enableVRF == 1 && $User->get_module_permissions ("vrf")>0) {
				$html[] = '<th data-field="vrf">'._('VRF').'</th>';
			}
			$html[] = '<th data-field="masterSubnet">'._('Master Subnet').'</th>';
			if($User->get_module_permissions ("devices")>0)
			$html[] = '<th data-field="device">'._('Device').'</th>';
			if($User->settings->enableCustomers == 1 && $User->get_module_permissions ("customers")>0) {
				$html[] = '<th data-field="customer" class="hidden-xs hidden-sm">'._('Customer').'</th>';
			}
			if(is_array($custom)) {
				foreach($custom as $field) {
					if(!in_array($field['name'], $hidden_fields)) {
						$html[] = '<th data-field="'.urlencode($field['name']).'" class="hidden-xs hidden-sm">'.$Tools->print_custom_field_name($field['name']).'</th>';
					}
				}
			}

			$html[] = '<th data-field="buttons" class="actions" data-width="140"></th>';
			$html[] = '</tr></thead></table>';

			if ($showSupernetOnly==='1') {
				$html[] = "<div class='alert alert-info'><i class='fa fa-info'></i> "._('Only master subnets are shown').'</div>';
			}
		} else {
			$html[] = "<div class='alert alert-danger'>"._('You do not have permission to access this network').'!</div>';
		}

		return implode("\n", $html);
	}
