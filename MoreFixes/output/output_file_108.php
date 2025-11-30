function cvs_get_revisions($project, $offset, $chunksz, $_tag = 100, $_branch = 100, $_commit_id = '', $_commiter = 100, $_srch = '', array $order_by = [], $pv = 0)
{
    //if status selected, and more to where clause
    if ($_branch != 100) {
        //for open tasks, add status=100 to make sure we show all
        $branch_str = "AND cvs_checkins.branchid=" . db_ei($_branch);
    } else {
        //no status was chosen, so don't add it to where clause
        $branch_str = '';
    }

    //if assigned to selected, and more to where clause
    if ($_commit_id != '') {
        $_commit_id = db_ei($_commit_id);
        $commit_str = "AND cvs_commits.id=$_commit_id AND cvs_checkins.commitid != 0 ";
    } else {
        $commit_str = '';
    }

    if ($_commiter != 100) {
        $_commiter    = db_es($_commiter);
        $commiter_str = "AND user.user_id=cvs_checkins.whoid " .
          "AND user.user_name='$_commiter' ";
    } else {
        //no assigned to was chosen, so don't add it to where clause
        $commiter_str = '';
    }

    if ($_srch != '') {
        $_srch    = db_es('%' . $_srch . '%');
        $srch_str = "AND cvs_descs.description like '$_srch' ";
    } else {
        $srch_str = "";
    }

    //build page title to make bookmarking easier
    //if a user was selected, add the user_name to the title
    //same for status

    //commits_header(array('title'=>'Browse Commits'.
    //    (($_assigned_to)?' For: '.user_getname($_assigned_to):'').
    //    (($_tag && ($_tag != 100))?' By Status: '. get_commits_status_nam//e($_status):''),
    //           'help' => 'CommitsManager.html'));

    // get repository id
    $cvs_repository = db_es('/cvsroot/' . $project->getUnixName(false));
    $query          = "SELECT id from cvs_repositories where cvs_repositories.repository='$cvs_repository' ";
    $rs             = db_query($query);
    $repo_id        = db_result($rs, 0, 0);
    $repo_id        = $repo_id ? $repo_id : -1;

    $select = 'SELECT distinct cvs_checkins.commitid as id, cvs_checkins.commitid as revision, cvs_descs.id as did, cvs_descs.description, cvs_commits.comm_when as c_when, cvs_commits.comm_when as date, cvs_commits.comm_when as f_when, user.user_name as who ';
    $from   = "FROM cvs_descs, cvs_checkins, user, cvs_commits ";
    $where  = "WHERE cvs_checkins.descid=cvs_descs.id " .
    "AND " . (check_cvs_access(user_getname(), $project->getUnixName(false), '') ? 1 : 0) . " " .
