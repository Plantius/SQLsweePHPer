    public function grid($dataSetId)
    {
        $dataSet = $this->dataSetFactory->getById($dataSetId);

        if (!$this->getUser()->checkEditable($dataSet))
            throw new AccessDeniedException();

        $sorting = $this->gridRenderSort();

        if ($sorting != null)
            $sorting = implode(',', $sorting);

        // Filter criteria
        $filter = '';
        foreach ($dataSet->getColumn() as $column) {
            /* @var \Xibo\Entity\DataSetColumn $column */
            if ($column->dataSetColumnTypeId == 1) {
                if ($this->getSanitizer()->getString($column->heading) != null) {
                    $filter .= 'AND ' . $column->heading . ' LIKE \'%' . $this->getSanitizer()->getString($column->heading) . '%\' ';
                }
            }
        }
        $filter = trim($filter, 'AND');

        // Work out the limits
        $filter = $this->gridRenderFilter(['filter' => $this->getSanitizer()->getParam('filter', $filter)]);

        try {
            $data = $dataSet->getData([
                'order' => $sorting,
                'start' => $filter['start'],
                'size' => $filter['length'],
                'filter' => $filter['filter']
            ]);
        } catch (\Exception $e) {
            $data = ['exception' => __('Error getting DataSet data, failed with following message: ') . $e->getMessage()];
            $this->getLog()->error('Error getting DataSet data, failed with following message: ' . $e->getMessage());
            $this->getLog()->debug($e->getTraceAsString());
        }

        $this->getState()->template = 'grid';
        $this->getState()->setData($data);

        // Output the count of records for paging purposes
        if ($dataSet->countLast() != 0)
            $this->getState()->recordsTotal = $dataSet->countLast();

        // Set this dataSet as being active
        $dataSet->setActive();
    }
