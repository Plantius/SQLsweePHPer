function init_args()
{
  $_REQUEST = strings_stripSlashes($_REQUEST);
    
  $args = new stdClass();
  $args->show_help = (isset($_REQUEST['level']) && $_REQUEST['level']=='testproject');
    
  $args->tproject_id = intval(isset($_REQUEST['tproject_id']) ? $_REQUEST['tproject_id'] : $_SESSION['testprojectID']);
  $args->tplan_id = intval(isset($_REQUEST['tplan_id']) ? $_REQUEST['tplan_id'] : $_SESSION['testplanID']);
  $args->tplan_name = $_SESSION['testplanName'];
  $args->node_type = isset($_REQUEST['level']) ? $_REQUEST['level'] : OFF;
  $args->node_id = isset($_REQUEST['id']) ? $_REQUEST['id'] : ERROR;

  // Sets urgency for suite
 
  if (isset($_REQUEST['high_urgency']))
  {  
    $args->urgency = HIGH;
  }
  elseif (isset($_REQUEST['medium_urgency']))
  {  
    $args->urgency = MEDIUM;
  }
  elseif (isset($_REQUEST['low_urgency']))
  {  
    $args->urgency = LOW;
  }
  else
  {
    $args->urgency = OFF;
  }  

  // Sets urgency for every single tc
  if (isset($_REQUEST['urgency'])) 
  {
    $args->urgency_tc = $_REQUEST['urgency'];
  }

  // For more information about the data accessed in session here, see the comment
  // in the file header of lib/functions/tlTestCaseFilterControl.class.php.
  $args->treeFormToken = isset($_REQUEST['form_token']) ? $_REQUEST['form_token'] : 0;
  $mode = 'plan_mode';
  $session_data = isset($_SESSION[$mode]) && isset($_SESSION[$mode][$args->treeFormToken]) ? 
                  $_SESSION[$mode][$args->treeFormToken] : null;


  $args->testCaseSet = $session_data['testcases_to_show'];
  $args->build4testers = intval($session_data['setting_build']);
  $args->platform_id = intval($session_data['setting_platform']);
      
  return $args;
}
