	public static function updateLayoutSlot($instanceId, $slotName, $templateFamily = false, $layoutId, $moduleId = false, $cID = false, $cType = false, $cVersion = false, $copySwatchUp = false, $copySwatchDown = false) {
	
		if ($cID && $cType && !$cVersion) {
			$cVersion = \ze\content::latestVersion($cID, $cType);
		}
	
		if (!$moduleId && $instanceId) {
			$details = \ze\plugin::details($instanceId);
			$moduleId = $details['module_id'];
		}
		
		if (!$templateFamily) {
			$templateFamily = \ze\row::get('layouts', 'family_name', $layoutId);
		}
	
		if ($moduleId) {
			$placementId = \ze\row::set(
				'plugin_layout_link',
				[
					'module_id' => $moduleId,
					'instance_id' => $instanceId],
				[
					'slot_name' => $slotName,
					'family_name' => $templateFamily,
					'layout_id' => $layoutId]);
		
		} else {
			\ze\row::delete(
				'plugin_layout_link',
				[
					'slot_name' => $slotName,
					'family_name' => $templateFamily,
					'layout_id' => $layoutId]);
		}
	}
