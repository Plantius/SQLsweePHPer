    static function getOrder($order) {
        if($_REQUEST['order'] && $orderWays[strtoupper($_REQUEST['order'])]) {
            $order=$orderWays[strtoupper($_REQUEST['order'])];
        }
        $order=$order?$order:'DESC';

        return $order;
    }
