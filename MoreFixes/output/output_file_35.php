            $project = $pm->getProject($project_id);
            if ($project->usesSVN()) {
                $html .= '<div class="project-last-commit-project-title">';
                list($hide_now,$count_diff,$hide_url) = my_hide_url('my_svn_group', $project_id, $request->get('hide_item_id'), count($project_ids), $request->get('hide_my_svn_group'), $request->get('dashboard_id'));
                $html .= $hide_url;

                $html .= '<strong>' . $hp->purify($project->getPublicName()) . '</strong>';
                if (! $hide_now) {
                    list($latest_revisions, $nb_revisions) = svn_get_revisions($project, 0, $this->_nb_svn_commits, '', $user->getUserName(), '', '', 0, false);
                    $revision_total += $nb_revisions;
                    if (db_numrows($latest_revisions) > 0) {
                        $i = 0;
                        while ($data = db_fetch_array($latest_revisions)) {
                            $html .= '<div class="' . util_get_alt_row_color($i++) . '" style="border-bottom:1px solid #ddd">';
                            $html .= '<div style="font-size:0.98em;" class="project-last-commit-text">';
                            $html .= '<a href="' . $this->_getLinkToCommit($project->getGroupId(), $data['revision']) . '">rev #' . $data['revision'] . '</a>';
                            $html .= ' ' . $GLOBALS['Language']->getText('my_index', 'my_latest_svn_commit_on') . ' ';
                            //In the db, svn dates are stored as int whereas cvs dates are stored as timestamp
                            $html .= format_date($GLOBALS['Language']->getText('system', 'datefmt'), (is_numeric($data['date']) ? $data['date'] : strtotime($data['date'])));
                            $html .= ' ' . $GLOBALS['Language']->getText('my_index', 'my_latest_svn_commit_by') . ' ';
                            if (isset($data['whoid'])) {
                                $name = $uh->getDisplayNameFromUserId($data['whoid']);
                            } else {
                                $name = $uh->getDisplayNameFromUserName($data['who']);
                            }
                            $html .= $hp->purify($name, CODENDI_PURIFIER_CONVERT_HTML);
                            $html .= '</div>';
                            $html .= '<div style="padding-left:20px; padding-bottom:4px; color:#555">';
                            $html .= $hp->purify(substr($data['description'], 0, 255), CODENDI_PURIFIER_BASIC_NOBR, $project->getGroupId());
                            if (strlen($data['description']) > 255) {
                                $html .= '&nbsp;[...]';
                            }
                            $html .= '</div>';
                            $html .= '</div>';
                        }
                        $html .= '<div style="text-align:center" class="' . util_get_alt_row_color($i++) . ' project-last-commit-text-more">';
                        $html .= '<a href="' . $this->_getLinkToMore($project->getGroupId(), $user->getUserName()) . '">[ More ]</a>';
                        $html .= '</div>';
                    } else {
                        $html .= '<div>' .
                            $GLOBALS['Language']->getText('my_index', 'my_latest_commit_empty') . '</div>';
                    }
                } else {
                    $html .= '<div></div>';
                }

                $html .=  '</div>';
            }
        }

        if ($revision_total === 0) {
            $html .= $GLOBALS['Language']->getText('my_index', 'my_latest_commit_empty');
        }
        return $html;
    }
