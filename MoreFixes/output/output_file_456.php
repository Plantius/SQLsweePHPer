    public function inheritableSegments(Request $request, SegmentManagerInterface $segmentManager)
    {
        $id = $request->get('id') ?? '';
        $type = $request->get('type') ?? '';

        $db = \Pimcore\Db::get();
        $parentIdStatement = sprintf('SELECT `%s` FROM `%s` WHERE `%s` = :value', $type === 'object' ? 'o_parentId' : 'parentId', $type.'s', $type === 'object' ? 'o_id' : 'id');
        $parentId = $db->fetchOne($parentIdStatement, [
            'value' => $id
        ]);

        $segments = $segmentManager->getSegmentsForElementId($parentId, $type);
        $data = array_map([$this, 'dehydrateSegment'], array_filter($segments));

        return $this->adminJson(['data' => array_values($data)]);
    }
