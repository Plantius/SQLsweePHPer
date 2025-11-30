function getHistory($symbol, $startDate, $endDate, $pdo) {
    $history = array();
    $query = "select date, EOD, MA20, MA50, delta, deltaMA5, deltaMA10, deltaMA20, P0, P1, P2, M1, M2, M3 from $symbol where date >= $startDate and date <= $endDate order by date DESC";
    $stmt = $pdo->query($query);
    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
}
