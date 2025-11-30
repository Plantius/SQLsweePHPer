function svn_utils_criteria_list_to_query($criteria_list)
{
    $criteria_list = str_replace('>', ' ASC', $criteria_list);
    $criteria_list = str_replace('<', ' DESC', $criteria_list);
    return $criteria_list;
}
