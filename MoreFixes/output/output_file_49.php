                $strArr[] = array('str' => $tmpStr, 'sort_order' => $sortOrder);
            }
            usort(
                $strArr,
                function ($a, $b) {
                    return $a['sort_order'] - $b['sort_order'];
                }
            );
            foreach ($strArr as $bits) {
                $str .= $bits['str'];
            }
            $str .= '</dl>';

            return $str;
        case "DateTime":
            return $responseArr[0]->answer_datetime;
        case "Date":
            $date = $timedate->fromUser($responseArr[0]->answer_datetime);
            if (!$date) {
                return $responseArr[0]->answer_datetime;
            } else {
                $date = $timedate->tzGMT($date);

                return $timedate->asUserDate($date);
            }
            // no break
        case "Rating":
            return str_repeat('<img width=20 src="modules/Surveys/imgs/star.png"/>', $responseArr[0]->answer);
        case "Scale":
            return $responseArr[0]->answer . '/10';
        case "Textbox":
        case "Text":
        default:
            return $responseArr[0]->answer;
    }
}
