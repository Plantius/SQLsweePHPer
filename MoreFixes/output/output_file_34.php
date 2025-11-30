			$pId = explode('_',$tagId);
			$pIdArr = $pId[1];
		}
		$checkIfPublishsql = "SELECT id,scheduled_publish_datetime from ". DB_PREFIX. "content_item_versions 
					WHERE id IN (". ze\escape::in($pIdArr, 'checkIfPublishsql'). ")
			  AND scheduled_publish_datetime IS NOT NULL
			  AND published_datetime IS NULL AND publisher_id=0" ;
		$checkIfPublish = ze\sql::select($checkIfPublishsql);
		$getresult = ze\sql::fetchAssoc($checkIfPublish);
		if($getresult && $checkIfPublish)
		{
			if(sizeof($getresult)>1 )
			{
				$values['publish/publish_options'] = 'schedule';
			
					$sdate = $getresult['scheduled_publish_datetime'];
					$sdate = strtotime($sdate);
					$values['publish/publish_hours'] = date('G', $sdate);
					$values['publish/publish_mins'] = date('i', $sdate);
					$values['publish/publish_date'] = date('Y-m-d',$sdate);
					
					$box['tabs']['publish']['notices']['scheduled_warning']['show'] = true;
					$box['tabs']['publish']['notices']['scheduled_warning']['message'] = "This item is scheduled to be published at " .ze\admin::formatDateTime($getresult['scheduled_publish_datetime'],'vis_date_format_med').".";
				
			}
			
		}
		
	}
