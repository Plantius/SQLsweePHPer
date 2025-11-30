    public function action_Tooltips()
    {
        global $mod_strings;

        $start_date = $_REQUEST['start_date'];
        $end_date = $_REQUEST['end_date'];
        $resource_id = $_REQUEST['resource_id'];

        $projects = explode(",", $_REQUEST['projects']);
        $project_where = "";
        if (count($projects) > 1 || $projects[0] != '') {
            $project_where = " AND project_id IN( '" . implode("','", $projects) . "' )";
        }

        $Task = BeanFactory::getBean('ProjectTask');
        
        $tasks = $Task->get_full_list("date_start", "project_task.assigned_user_id = '".$resource_id."' AND ( ( project_task.date_start BETWEEN '".$start_date."'  AND '".$end_date."' ) OR ( project_task.date_finish BETWEEN '".$start_date."' AND '".$end_date."' ) OR ( '".$start_date."' BETWEEN project_task.date_start  AND project_task.date_finish ) OR ( '".$end_date."' BETWEEN project_task.date_start AND project_task.date_finish ) ) AND (project_id is not null AND project_id <> '') " . $project_where);

        echo '<table class="qtip_table">';
        echo '<tr><th>'.$mod_strings['LBL_TOOLTIP_PROJECT_NAME'].'</th><th>'.$mod_strings['LBL_TOOLTIP_TASK_NAME'].'</th><th>'.$mod_strings['LBL_TOOLTIP_TASK_DURATION'].'</th></tr>';
        if (is_array($tasks)) {
            foreach ($tasks as $task) {
                echo '<tr><td><a target="_blank" href="index.php?module=Project&action=DetailView&record='.$task->project_id.'">'.$task->project_name.'</a></td><td>'.$task->name.'</td><td>'.$task->duration.' '.$task->duration_unit.'</td></tr>';
            }
        }
        echo '</table>';

        die();
    }
