function doOneDay($catid, $udate, $starttime, $duration, $prefcatid)
{
    global $slots, $slotsecs, $slotstime, $slotbase, $slotcount, $input_catid;
    $udate = strtotime($starttime, $udate);
    if ($udate < $slotstime) {
        return;
    }

    $i = (int)($udate / $slotsecs) - $slotbase;
    $iend = (int)(($duration + $slotsecs - 1) / $slotsecs) + $i;
    if ($iend > $slotcount) {
        $iend = $slotcount;
    }

    if ($iend <= $i) {
        $iend = $i + 1;
    }

    for (; $i < $iend; ++$i) {
        if ($catid == 2) {        // in office
            // If a category ID was specified when this popup was invoked, then select
            // only IN events with a matching preferred category or with no preferred
            // category; other IN events are to be treated as OUT events.
            if ($input_catid) {
                if ($prefcatid == $input_catid || !$prefcatid) {
                    $slots[$i] |= 1;
                } else {
                    $slots[$i] |= 2;
                }
            } else {
                $slots[$i] |= 1;
            }

            break; // ignore any positive duration for IN
        } elseif ($catid == 3) { // out of office
            $slots[$i] |= 2;
            break; // ignore any positive duration for OUT
        } else { // all other events reserve time
            $slots[$i] |= 4;
        }
    }
}
