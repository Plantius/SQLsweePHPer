    public function showAction(Request $request, Connection $db)
    {
        $qb = $db->createQueryBuilder();
        $qb
            ->select('*')
            ->from(ApplicationLoggerDb::TABLE_NAME)
            ->setFirstResult($request->get('start', 0))
            ->setMaxResults($request->get('limit', 50));

        $sortingSettings = QueryParams::extractSortingSettings(array_merge(
            $request->request->all(),
            $request->query->all()
        ));

        if ($sortingSettings['orderKey']) {
            $qb->orderBy($sortingSettings['orderKey'], $sortingSettings['order']);
        } else {
            $qb->orderBy('id', 'DESC');
        }

        $priority = $request->get('priority');
        if(!empty($priority)) {
            $qb->andWhere($qb->expr()->eq('priority', ':priority'));
            $qb->setParameter('priority', $priority);
        }

        if ($fromDate = $this->parseDateObject($request->get('fromDate'), $request->get('fromTime'))) {
            $qb->andWhere('timestamp > :fromDate');
            $qb->setParameter('fromDate', $fromDate, Types::DATETIME_MUTABLE);
        }

        if ($toDate = $this->parseDateObject($request->get('toDate'), $request->get('toTime'))) {
            $qb->andWhere('timestamp <= :toDate');
            $qb->setParameter('toDate', $toDate, Types::DATETIME_MUTABLE);
        }

        if (!empty($component = $request->get('component'))) {
            $qb->andWhere('component = ' . $qb->createNamedParameter($component));
        }

        if (!empty($relatedObject = $request->get('relatedobject'))) {
            $qb->andWhere('relatedobject = ' . $qb->createNamedParameter($relatedObject));
        }

        if (!empty($message = $request->get('message'))) {
            $qb->andWhere('message LIKE ' . $qb->createNamedParameter('%' . $message . '%'));
        }

        if (!empty($pid = $request->get('pid'))) {
            $qb->andWhere('pid LIKE ' . $qb->createNamedParameter('%' . $pid . '%'));
        }

        $totalQb = clone $qb;
        $totalQb->setMaxResults(null)
            ->setFirstResult(0)
            ->select('COUNT(id) as count');
        $total = $totalQb->execute()->fetch();
        $total = (int) $total['count'];

        $stmt = $qb->execute();
        $result = $stmt->fetchAllAssociative();

        $logEntries = [];
        foreach ($result as $row) {
            $fileobject = null;
            if ($row['fileobject']) {
                $fileobject = str_replace(PIMCORE_PROJECT_ROOT, '', $row['fileobject']);
            }

            $logEntry = [
                'id' => $row['id'],
                'pid' => $row['pid'],
                'message' => $row['message'],
                'timestamp' => $row['timestamp'],
                'priority' => $row['priority'],
                'fileobject' => $fileobject,
                'relatedobject' => $row['relatedobject'],
                'relatedobjecttype' => $row['relatedobjecttype'],
                'component' => $row['component'],
                'source' => $row['source'],
            ];

            $logEntries[] = $logEntry;
        }

        return $this->adminJson([
            'p_totalCount' => $total,
            'p_results' => $logEntries,
        ]);
    }
