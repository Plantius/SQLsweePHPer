            $input              = $this->_filter_input($rule[2], $type, $operator);
            $sql_match_operator = $operator['sql'] ?? '';

            switch ($rule[0]) {
                case 'title':
                    $where[]    = "(`artist`.`name` $sql_match_operator ? OR LTRIM(CONCAT(COALESCE(`artist`.`prefix`, ''), ' ', `artist`.`name`)) $sql_match_operator ?)";
                    $parameters = array_merge($parameters, array($input, $input));
                    break;
                case 'yearformed':
                case 'placeformed':
                    $where[]      = "`artist`.`$rule[0]` $sql_match_operator ?";
                    $parameters[] = $input;
                    break;
                case 'time':
                    $input        = $input * 60;
                    $where[]      = "`artist`.`time` $sql_match_operator ?";
                    $parameters[] = $input;
                    break;
                case 'genre':
                    $where[]      = "`artist`.`id` IN (SELECT `tag_map`.`object_id` FROM `tag_map` LEFT JOIN `tag` ON `tag_map`.`tag_id` = `tag`.`id` AND `tag`.`is_hidden` = 0 AND `tag`.`name` $sql_match_operator ? WHERE `tag_map`.`object_type`='artist' AND `tag`.`id` IS NOT NULL)";
                    $parameters[] = $input;
                    break;
                case 'song_genre':
                    $where[]      = "`song`.`id` IN (SELECT `tag_map`.`object_id` FROM `tag_map` LEFT JOIN `tag` ON `tag_map`.`tag_id` = `tag`.`id` AND `tag`.`is_hidden` = 0 AND `tag`.`name` $sql_match_operator ? WHERE `tag_map`.`object_type`='song' AND `tag`.`id` IS NOT NULL)";
                    $parameters[] = $input;
                    $join['song'] = true;
                    break;
                case 'no_genre':
                    $where[] = "`artist`.`id` NOT IN (SELECT `tag_map`.`object_id` FROM `tag_map` LEFT JOIN `tag` ON `tag_map`.`tag_id` = `tag`.`id` AND `tag`.`is_hidden` = 0 WHERE `tag_map`.`object_type`='artist' AND `tag`.`id` IS NOT NULL)";
                    break;
                case 'playlist_name':
                    $where[]    = "(`artist`.`id` IN (SELECT `artist_map`.`artist_id` FROM `playlist_data` LEFT JOIN `playlist` ON `playlist_data`.`playlist` = `playlist`.`id` LEFT JOIN `song` ON `song`.`id` = `playlist_data`.`object_id` AND `playlist_data`.`object_type` = 'song' LEFT JOIN `artist_map` ON `artist_map`.`object_id` = `song`.`id` AND `artist_map`.`object_type` = 'song' WHERE `playlist`.`name` $sql_match_operator ?) OR `artist`.`id` IN (SELECT `artist_map`.`artist_id` FROM `playlist_data` LEFT JOIN `playlist` ON `playlist_data`.`playlist` = `playlist`.`id` LEFT JOIN `song` ON `song`.`id` = `playlist_data`.`object_id` AND `playlist_data`.`object_type` = 'song' LEFT JOIN `artist_map` ON `artist_map`.`object_id` = `song`.`album` AND `artist_map`.`object_type` = 'album' WHERE `playlist`.`name` $sql_match_operator ?))";
                    $parameters = array_merge($parameters, array($input, $input));
                    break;
                case 'playlist':
                    $where[]    = "(`artist`.`id` $sql_match_operator IN (SELECT `artist_map`.`artist_id` FROM `playlist_data` LEFT JOIN `song` ON `song`.`id` = `playlist_data`.`object_id` AND `playlist_data`.`object_type` = 'song' LEFT JOIN `artist_map` ON `artist_map`.`object_id` = `song`.`id` AND `artist_map`.`object_type` = 'song' WHERE `playlist_data`.`playlist` = ?) OR `artist`.`id` $sql_match_operator IN (SELECT `artist_map`.`artist_id` FROM `playlist_data` LEFT JOIN `song` ON `song`.`id` = `playlist_data`.`object_id` AND `playlist_data`.`object_type` = 'song' LEFT JOIN `artist_map` ON `artist_map`.`object_id` = `song`.`id` AND `artist_map`.`object_type` = 'song' WHERE `playlist_data`.`playlist` = ?))";
                    $parameters = array_merge($parameters, array($input, $input));
                    break;
                case 'rating':
                    // average ratings only
                    $where[]          = "`average_rating`.`avg` $sql_match_operator ?";
                    $parameters[]     = $input;
                    $table['average'] = "LEFT JOIN (SELECT `object_id`, ROUND(AVG(IFNULL(`rating`.`rating`,0))) AS `avg` FROM `rating` WHERE `rating`.`object_type`='artist' GROUP BY `object_id`) AS `average_rating` ON `average_rating`.`object_id` = `artist`.`id` ";
                    break;
                case 'favorite':
                    $where[]    = "(`artist`.`name` $sql_match_operator ? OR LTRIM(CONCAT(COALESCE(`artist`.`prefix`, ''), ' ', `artist`.`name`)) $sql_match_operator ?) AND `favorite_artist_$user_id`.`user` = $user_id AND `favorite_artist_$user_id`.`object_type` = 'artist'";
                    $parameters = array_merge($parameters, array($input, $input));
                    // flag once per user
                    if (!array_key_exists('favorite', $table)) {
                        $table['favorite'] = '';
                    }
                    $table['favorite'] .= (!strpos((string) $table['favorite'], "favorite_artist_$user_id"))
                        ? "LEFT JOIN (SELECT `object_id`, `object_type`, `user` FROM `user_flag` WHERE `user` = $user_id) AS `favorite_artist_$user_id` ON `artist`.`id` = `favorite_artist_$user_id`.`object_id` AND `favorite_artist_$user_id`.`object_type` = 'artist'"
                        : "";
                    break;
                case 'file':
                    $where[]      = "`song`.`file` $sql_match_operator ?";
                    $parameters[] = $input;
                    $join['song'] = true;
                    break;
                case 'has_image':
                    $where[]            = ($sql_match_operator == '1') ? "`has_image`.`object_id` IS NOT NULL" : "`has_image`.`object_id` IS NULL";
                    $table['has_image'] = "LEFT JOIN (SELECT `object_id` FROM `image` WHERE `object_type` = 'artist') AS `has_image` ON `artist`.`id` = `has_image`.`object_id`";
                    break;
                case 'image_height':
                case 'image_width':
                    $looking       = strpos($rule[0], "image_") ? str_replace('image_', '', $rule[0]) : str_replace('image ', '', $rule[0]);
                    $where[]       = "`image`.`$looking` $sql_match_operator ?";
                    $parameters[]  = $input;
                    $join['image'] = true;
                    break;
                case 'myrating':
                    $column  = 'id';
                    $my_type = 'artist';
                    if ($input == 0 && $sql_match_operator == '>=') {
                        break;
                    }
                    if ($input == 0 && $sql_match_operator == '<') {
                        $input              = -1;
                        $sql_match_operator = '=';
                    }
                    if ($input == 0 && $sql_match_operator == '<>') {
                        $input              = 1;
                        $sql_match_operator = '>=';
                    }
                    if (($input == 0 && $sql_match_operator != '>') || ($input == 1 && $sql_match_operator == '<')) {
                        $where[] = "`rating_" . $my_type . "_" . $user_id . "`.`rating` IS NULL";
                    } elseif (in_array($sql_match_operator, array('<>', '<', '<=', '!='))) {
                        $where[]      = "(`rating_" . $my_type . "_" . $user_id . "`.`rating` $sql_match_operator ? OR `rating_" . $my_type . "_" . $user_id . "`.`rating` IS NULL)";
                        $parameters[] = $input;
                    } else {
                        $where[]      = "`rating_" . $my_type . "_" . $user_id . "`.`rating` $sql_match_operator ?";
                        $parameters[] = $input;
                    }
                    // rating once per user
                    if (!array_key_exists('rating', $table)) {
                        $table['rating'] = '';
                    }
                    $table['rating'] .= (!strpos((string) $table['rating'], "rating_" . $my_type . "_" . $user_id))
                        ? "LEFT JOIN (SELECT `object_id`, `object_type`, `rating` FROM `rating` WHERE `user` = $user_id AND `object_type`='$my_type') AS `rating_" . $my_type . "_" . $user_id . "` ON `rating_" . $my_type . "_" . $user_id . "`.`object_id` = `artist`.`$column`"
                        : "";
                    break;
                case 'albumrating':
                case 'songrating':
                    $looking = str_replace('rating', '', $rule[0]);
                    $column  = ($looking == 'album') ? 'album_artist' : 'artist';
                    if ($input == 0 && $sql_match_operator == '>=') {
                        break;
                    }
                    if ($input == 0 && $sql_match_operator == '<') {
                        $input              = -1;
                        $sql_match_operator = '<=>';
                    }
                    if ($input == 0 && $sql_match_operator == '<>') {
                        $input              = 1;
                        $sql_match_operator = '>=';
                    }
                    if (($input == 0 && $sql_match_operator != '>') || ($input == 1 && $sql_match_operator == '<')) {
                        $where[] = "`artist`.`id` IN (SELECT `id` FROM `artist` WHERE `id` IN (SELECT `$looking`.`$column` FROM `$looking` WHERE `id` NOT IN (SELECT `object_id` FROM `rating` WHERE `user` = $user_id AND `object_type`='$looking')))";
                    } elseif (in_array($sql_match_operator, array('<>', '<', '<=', '!='))) {
                        $where[]      = "`artist`.`id` IN (SELECT `id` FROM `artist` WHERE `id` IN (SELECT `$looking`.`$column` FROM `$looking` WHERE `id` IN (SELECT `object_id` FROM `rating` WHERE `user` = $user_id AND `object_type`='$looking' AND `rating` $sql_match_operator ?))) OR `$looking`.`$column` NOT IN (SELECT `$column` FROM `$looking` WHERE `id` IN (SELECT `$column` FROM `$looking` WHERE `id` IN (SELECT `object_id` FROM `rating` WHERE `user` = $user_id AND `object_type`='$looking')))";
                        $parameters[] = $input;
                    } else {
                        $where[]      = "`artist`.`id` IN (SELECT `id` FROM `artist` WHERE `id` IN (SELECT `$looking`.`$column` FROM `$looking` WHERE `id` IN (SELECT `object_id` FROM `rating` WHERE `user` = $user_id AND `object_type`='$looking' AND `rating` $sql_match_operator ?)))";
                        $parameters[] = $input;
                    }
                    break;
                case 'myplayed':
                    $column       = 'id';
                    $my_type      = 'artist';
                    $operator_sql = ((int)$sql_match_operator == 0) ? 'IS NULL' : 'IS NOT NULL';
                    // played once per user
                    if (!array_key_exists('myplayed', $table)) {
                        $table['myplayed'] = '';
                    }
                    $table['myplayed'] .= (!strpos((string) $table['myplayed'], "myplayed_" . $my_type . "_" . $user_id))
                        ? "LEFT JOIN (SELECT DISTINCT `artist_map`.`artist_id`, `object_count`.`user` FROM `object_count` LEFT JOIN `artist_map` ON `object_count`.`object_type` = `artist_map`.`object_type` AND `artist_map`.`object_id` = `object_count`.`object_id` WHERE `object_count`.`count_type` = 'stream' AND `object_count`.`user`=$user_id GROUP BY `artist_map`.`artist_id`, `user`) AS `myplayed_" . $my_type . "_" . $user_id . "` ON `artist`.`$column` = `myplayed_" . $my_type . "_" . $user_id . "`.`artist_id`"
                        : "";
                    $where[] = "`myplayed_" . $my_type . "_" . $user_id . "`.`artist_id` $operator_sql";
                    break;
                case 'played':
                    $column       = 'id';
                    $my_type      = 'artist';
                    $operator_sql = ((int)$sql_match_operator == 0) ? 'IS NULL' : 'IS NOT NULL';
                    // played once per user
                    if (!array_key_exists('played', $table)) {
                        $table['played'] = '';
                    }
                    $table['played'] .= (!strpos((string) $table['played'], "played_" . $my_type))
                        ? "LEFT JOIN (SELECT DISTINCT `artist_map`.`artist_id`, `object_count`.`user` FROM `object_count` LEFT JOIN `artist_map` ON `object_count`.`object_type` = `artist_map`.`object_type` AND `artist_map`.`object_id` = `object_count`.`object_id` WHERE `object_count`.`object_type` = 'song' AND `object_count`.`count_type` = 'stream' GROUP BY `artist_map`.`artist_id`, `user`) AS `played_" . $my_type . "` ON `artist`.`$column` = `played_" . $my_type . "`.`artist_id`"
                        : "";
                    $where[] = "`played_" . $my_type . "`.`artist_id` $operator_sql";
                    break;
                case 'last_play':
                    $my_type = 'artist';
                    if (!array_key_exists('last_play', $table)) {
                        $table['last_play'] = '';
                    }
                    $table['last_play'] .= (!strpos((string) $table['last_play'], "last_play_" . $my_type . "_" . $user_id))
                        ? "LEFT JOIN (SELECT `object_id`, `object_type`, `user`, MAX(`date`) AS `date` FROM `object_count` WHERE `object_count`.`object_type` = '$my_type' AND `object_count`.`count_type` = 'stream' AND `object_count`.`user`=$user_id GROUP BY `object_id`, `object_type`, `user`) AS `last_play_" . $my_type . "_" . $user_id . "` ON `artist`.`id` = `last_play_" . $my_type . "_" . $user_id . "`.`object_id` AND `last_play_" . $my_type . "_" . $user_id . "`.`object_type` = '$my_type'"
                        : "";
                    $where[]      = "`last_play_" . $my_type . "_" . $user_id . "`.`date` $sql_match_operator (UNIX_TIMESTAMP() - (? * 86400))";
                    $parameters[] = $input;
                    break;
                case 'last_skip':
                    $my_type = 'artist';
                    if (!array_key_exists('last_skip', $table)) {
                        $table['last_skip'] = '';
                    }
                    $table['last_skip'] .= (!strpos((string) $table['last_skip'], "last_skip_" . $my_type . "_" . $user_id))
                        ? "LEFT JOIN (SELECT `object_id`, `object_type`, `user`, MAX(`date`) AS `date` FROM `object_count` WHERE `object_count`.`object_type` = 'song' AND `object_count`.`count_type` = 'skip' AND `object_count`.`user`=$user_id GROUP BY `object_id`, `object_type`, `user`) AS `last_skip_" . $my_type . "_" . $user_id . "` ON `song`.`id` = `last_skip_" . $my_type . "_" . $user_id . "`.`object_id` AND `last_skip_" . $my_type . "_" . $user_id . "`.`object_type` = 'song'"
                        : "";
                    $where[]      = "`last_skip_" . $my_type . "_" . $user_id . "`.`date` $sql_match_operator (UNIX_TIMESTAMP() - (? * 86400))";
                    $parameters[] = $input;
                    $join['song'] = true;
                    break;
                case 'last_play_or_skip':
                    $my_type = 'artist';
                    if (!array_key_exists('last_play_or_skip', $table)) {
                        $table['last_play_or_skip'] = '';
                    }
                    $table['last_play_or_skip'] .= (!strpos((string) $table['last_play_or_skip'], "last_play_or_skip_" . $my_type . "_" . $user_id))
                        ? "LEFT JOIN (SELECT `object_id`, `object_type`, `user`, MAX(`date`) AS `date` FROM `object_count` WHERE `object_count`.`object_type` = 'song' AND `object_count`.`count_type` IN ('stream', 'skip') AND `object_count`.`user`=$user_id GROUP BY `object_id`, `object_type`, `user`) AS `last_play_or_skip_" . $my_type . "_" . $user_id . "` ON `song`.`id` = `last_play_or_skip_" . $my_type . "_" . $user_id . "`.`object_id` AND `last_play_or_skip_" . $my_type . "_" . $user_id . "`.`object_type` = 'song'"
                        : "";
                    $where[]      = "`last_play_or_skip_" . $my_type . "_" . $user_id . "`.`date` $sql_match_operator (UNIX_TIMESTAMP() - (? * 86400))";
                    $parameters[] = $input;
                    $join['song'] = true;
                    break;
                case 'played_times':
                    $where[]      = "(`artist`.`total_count` $sql_match_operator ?)";
                    $parameters[] = $input;
                    break;
                case 'summary':
                    $where[]      = "`artist`.`summary` $sql_match_operator ?";
                    $parameters   = array_merge($parameters, array($input));
                    break;
                case 'album':
                    $where[]       = "(`album`.`name` $sql_match_operator ? OR LTRIM(CONCAT(COALESCE(`album`.`prefix`, ''), ' ', `album`.`name`)) $sql_match_operator ?) AND `artist_map`.`artist_id` IS NOT NULL";
                    $parameters    = array_merge($parameters, array($input, $input));
                    $join['album'] = true;
                    break;
                case 'song':
                    $where[]      = "`song`.`title` $sql_match_operator ?";
                    $parameters   = array_merge($parameters, array($input));
                    $join['song'] = true;
                    break;
                case 'album_count':
                    $group_column = (AmpConfig::get('album_group')) ? '`artist`.`album_group_count`' : '`artist`.`album_count`';
                    $where[]      = "($group_column $sql_match_operator ?)";
                    $parameters[] = $input;
                    break;
                case 'song_count':
                    $where[]      = "(`artist`.`song_count` $sql_match_operator ?)";
                    $parameters[] = $input;
                    break;
                case 'other_user':
                    $other_userid = $input;
                    if ($sql_match_operator == 'userflag') {
                        $where[] = "`favorite_artist_$other_userid`.`user` = $other_userid AND `favorite_artist_$other_userid`.`object_type` = 'artist'";
                        // flag once per user
                        if (!array_key_exists('favorite', $table)) {
                            $table['favorite'] = '';
                        }
                        $table['favorite'] .= (!strpos((string) $table['favorite'], "favorite_artist_$other_userid"))
                            ? "LEFT JOIN (SELECT `object_id`, `object_type`, `user` FROM `user_flag` WHERE `user` = $other_userid) AS `favorite_artist_$other_userid` ON `artist`.`id` = `favorite_artist_$other_userid`.`object_id` AND `favorite_artist_$other_userid`.`object_type` = 'artist'"
                            : "";
                    } else {
                        $column  = 'id';
                        $my_type = 'artist';
                        $where[] = "`rating_artist_" . $other_userid . '`.' . $sql_match_operator . " AND `rating_artist_$other_userid`.`user` = $other_userid AND `rating_artist_$other_userid`.`object_type` = 'artist'";
                        // rating once per user
                        if (!array_key_exists('rating', $table)) {
                            $table['rating'] = '';
                        }
                        $table['rating'] .= (!strpos((string) $table['rating'], "rating_" . $my_type . "_" . $user_id))
                            ? "LEFT JOIN `rating` AS `rating_" . $my_type . "_" . $user_id . "` ON `rating_" . $my_type . "_" . $user_id . "`.`object_type`='$my_type' AND `rating_" . $my_type . "_" . $user_id . "`.`object_id` = `$my_type`.`$column` AND `rating_" . $my_type . "_" . $user_id . "`.`user` = $user_id "
                            : "";
                    }
                    break;
                case 'recent_played':
                    $key                     = md5($input . $sql_match_operator);
                    $where[]                 = "`played_$key`.`object_id` IS NOT NULL";
                    $table['played_' . $key] = "LEFT JOIN (SELECT `object_id` FROM `object_count` WHERE `object_type` = 'artist' ORDER BY $sql_match_operator DESC LIMIT $input) AS `played_$key` ON `artist`.`id` = `played_$key`.`object_id`";
                    break;
                case 'catalog':
                    $where[]         = "`catalog_se`.`id` $sql_match_operator ?";
                    $parameters[]    = $input;
                    $join['catalog'] = true;
                    break;
                case 'mbid':
                    if (!$input || $input == '%%' || $input == '%') {
                        if (in_array($sql_match_operator, array('=', 'LIKE', 'SOUNDS LIKE'))) {
                            $where[]      = "`artist`.`mbid` IS NULL";
                            break;
                        }
                        if (in_array($sql_match_operator, array('!=', 'NOT LIKE', 'NOT SOUNDS LIKE'))) {
                            $where[]      = "`artist`.`mbid` IS NOT NULL";
                            break;
                        }
                    }
                    $where[]      = "`artist`.`mbid` $sql_match_operator ?";
                    $parameters[] = $input;
                    break;
                case 'mbid_album':
                    if (!$input || $input == '%%' || $input == '%') {
                        if (in_array($sql_match_operator, array('=', 'LIKE', 'SOUNDS LIKE'))) {
                            $where[]      = "`album`.`mbid` IS NULL";
                            break;
                        }
                        if (in_array($sql_match_operator, array('!=', 'NOT LIKE', 'NOT SOUNDS LIKE'))) {
                            $where[]      = "`album`.`mbid` IS NOT NULL";
                            break;
                        }
                    }
                    $where[]       = "`album`.`mbid` $sql_match_operator ?";
                    $parameters[]  = $input;
                    $join['album'] = true;
                    break;
                case 'mbid_song':
                    if (!$input || $input == '%%' || $input == '%') {
                        if (in_array($sql_match_operator, array('=', 'LIKE', 'SOUNDS LIKE'))) {
                            $where[]      = "`song`.`mbid` IS NULL";
                            break;
                        }
                        if (in_array($sql_match_operator, array('!=', 'NOT LIKE', 'NOT SOUNDS LIKE'))) {
                            $where[]      = "`song`.`mbid` IS NOT NULL";
                            break;
                        }
                    }
                    $where[]      = "`song`.`mbid` $sql_match_operator ?";
                    $parameters[] = $input;
                    $join['song'] = true;
                    break;
                case 'possible_duplicate':
                    $where[]               = "(`dupe_search1`.`dupe_id1` IS NOT NULL OR `dupe_search2`.`dupe_id2` IS NOT NULL)";
                    $table['dupe_search1'] = "LEFT JOIN (SELECT MIN(`id`) AS `dupe_id1`, LTRIM(CONCAT(COALESCE(`artist`.`prefix`, ''), ' ', `artist`.`name`)) AS `fullname`, COUNT(LTRIM(CONCAT(COALESCE(`artist`.`prefix`, ''), ' ', `artist`.`name`))) AS `Counting` FROM `artist` GROUP BY `fullname` HAVING `Counting` > 1) AS `dupe_search1` ON `artist`.`id` = `dupe_search1`.`dupe_id1`";
                    $table['dupe_search2'] = "LEFT JOIN (SELECT MAX(`id`) AS `dupe_id2`, LTRIM(CONCAT(COALESCE(`artist`.`prefix`, ''), ' ', `artist`.`name`)) AS `fullname`, COUNT(LTRIM(CONCAT(COALESCE(`artist`.`prefix`, ''), ' ', `artist`.`name`))) AS `Counting` FROM `artist` GROUP BY `fullname` HAVING `Counting` > 1) AS `dupe_search2` ON `artist`.`id` = `dupe_search2`.`dupe_id2`";
                    break;
                case 'possible_duplicate_album':
                    $where[]                     = "((`dupe_album_search1`.`dupe_album_id1` IS NOT NULL OR `dupe_album_search2`.`dupe_album_id2` IS NOT NULL))";
                    $table['dupe_album_search1'] = "LEFT JOIN (SELECT `album_artist`, MIN(`id`) AS `dupe_album_id1`, LTRIM(CONCAT(COALESCE(`album`.`prefix`, ''), ' ', `album`.`name`)) AS `fullname`, COUNT(LTRIM(CONCAT(COALESCE(`album`.`prefix`, ''), ' ', `album`.`name`))) AS `Counting` FROM `album` GROUP BY `album_artist`, LTRIM(CONCAT(COALESCE(`album`.`prefix`, ''), ' ', `album`.`name`)), `disk`, `year`, `release_type`, `release_status` HAVING `Counting` > 1) AS `dupe_album_search1` ON `artist`.`id` = `dupe_album_search1`.`album_artist`";
                    $table['dupe_album_search2'] = "LEFT JOIN (SELECT `album_artist`, MAX(`id`) AS `dupe_album_id2`, LTRIM(CONCAT(COALESCE(`album`.`prefix`, ''), ' ', `album`.`name`)) AS `fullname`, COUNT(LTRIM(CONCAT(COALESCE(`album`.`prefix`, ''), ' ', `album`.`name`))) AS `Counting` FROM `album` GROUP BY `album_artist`, LTRIM(CONCAT(COALESCE(`album`.`prefix`, ''), ' ', `album`.`name`)), `disk`, `year`, `release_type`, `release_status` HAVING `Counting` > 1) AS `dupe_album_search2` ON `artist`.`id` = `dupe_album_search2`.`album_artist`";
                    break;
                default:
                    break;
            } // switch on ruletype artist
        } // foreach rule

        $join['catalog']     = array_key_exists('catalog', $join) || $catalog_disable || $catalog_filter;
        $join['catalog_map'] = $catalog_filter;

        $where_sql = implode(" $sql_logic_operator ", $where);

        if (array_key_exists('song', $join)) {
            $table['0_artist_map'] = "LEFT JOIN `artist_map` ON `artist_map`.`artist_id` = `artist`.`id`";
            $table['1_song']       = "LEFT JOIN `song` ON `artist_map`.`object_id` = `song`.`id` AND `artist_map`.`object_type` = 'song'";
        }
        if (array_key_exists('album', $join)) {
            $table['0_artist_map'] = "LEFT JOIN `artist_map` ON `artist_map`.`artist_id` = `artist`.`id`";
            $table['4_album_map']  = "LEFT JOIN `album_map` ON `album_map`.`object_id` = `artist`.`id` AND `artist_map`.`object_type` = `album_map`.`object_type`";
            $table['album']        = "LEFT JOIN `album` ON `album_map`.`album_id` = `album`.`id`";
        }
        if ($join['catalog']) {
            $table['2_catalog_map'] = "LEFT JOIN `catalog_map` AS `catalog_map_artist` ON `catalog_map_artist`.`object_id` = `artist`.`id` AND `catalog_map_artist`.`object_type` = 'artist'";
            $table['3_catalog']     = "LEFT JOIN `catalog` AS `catalog_se` ON `catalog_se`.`id` = `catalog_map_artist`.`catalog_id`";
            if (!empty($where_sql)) {
                $where_sql = "(" . $where_sql . ") AND `catalog_se`.`enabled` = '1'";
            } else {
                $where_sql = "`catalog_se`.`enabled` = '1'";
            }
        }
        if ($join['catalog_map']) {
            if (!empty($where_sql)) {
                $where_sql = "(" . $where_sql . ") AND `catalog_se`.`id` IN (SELECT `catalog_id` FROM `catalog_filter_group_map` INNER JOIN `user` ON `user`.`catalog_filter_group` = `catalog_filter_group_map`.`group_id` WHERE `user`.`id` = $user_id AND `catalog_filter_group_map`.`enabled`=1)";
            } else {
                $where_sql = "`catalog_se`.`id` IN (SELECT `catalog_id` FROM `catalog_filter_group_map` INNER JOIN `user` ON `user`.`catalog_filter_group` = `catalog_filter_group_map`.`group_id` WHERE `user`.`id` = $user_id AND `catalog_filter_group_map`.`enabled`=1)";
            }
        }
        if (array_key_exists('count', $join)) {
            $table['object_count'] = "LEFT JOIN (SELECT `object_count`.`object_id`, MAX(`object_count`.`date`) AS `date` FROM `object_count` WHERE `object_count`.`object_type` = 'artist' AND `object_count`.`user`='" . $user_id . "' AND `object_count`.`count_type` = 'stream' GROUP BY `object_count`.`object_id`) AS `object_count` ON `object_count`.`object_id` = `artist`.`id`";
        }
        if (array_key_exists('image', $join)) {
            $table['0_artist_map'] = "LEFT JOIN `artist_map` ON `artist_map`.`artist_id` = `artist`.`id`";
            $table['1_song']       = "LEFT JOIN `song` ON `artist_map`.`artist_id` = `artist`.`id` AND `artist_map`.`object_type` = 'song'";
            $where_sql             = "(" . $where_sql . ") AND `image`.`object_type`='artist' AND `image`.`size`='original'";
        }
        if ($album_artist) {
            if (!empty($where_sql)) {
                $where_sql = "(" . $where_sql . ") AND `artist`.`album_count` > 0";
            } else {
                $where_sql = "`artist`.`album_count` > 0";
            }
        }
        if ($song_artist) {
            if (!empty($where_sql)) {
                $where_sql = "(" . $where_sql . ") AND `artist`.`song_count` > 0";
            } else {
                $where_sql = "`artist`.`song_count` > 0";
            }
        }
        ksort($table);
        $table_sql  = implode(' ', $table);
        $group_sql  = implode(',', $group);
        $having_sql = implode(" $sql_logic_operator ", $having);

        return array(
            'base' => "SELECT DISTINCT(`artist`.`id`), `artist`.`name` FROM `artist`",
            'join' => $join,
            'where' => $where,
            'where_sql' => $where_sql,
            'table' => $table,
            'table_sql' => $table_sql,
            'group_sql' => $group_sql,
            'having_sql' => $having_sql,
            'parameters' => $parameters
        );
    }
