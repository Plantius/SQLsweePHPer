    public function downloadAsZipJobsAction(Request $request)
    {
        $jobId = uniqid();
        $filesPerJob = 5;
        $jobs = [];
        $asset = Asset::getById((int) $request->get('id'));

        if (!$asset) {
            throw $this->createNotFoundException('Asset not found');
        }

        if ($asset->isAllowed('view')) {
            $parentPath = $asset->getRealFullPath();
            if ($asset->getId() == 1) {
                $parentPath = '';
            }

            $db = \Pimcore\Db::get();
            $conditionFilters = [];
            $selectedIds = explode(',', $request->get('selectedIds', ''));
            $quotedSelectedIds = [];
            foreach ($selectedIds as $selectedId) {
                if ($selectedId) {
                    $quotedSelectedIds[] = $db->quote($selectedId);
                }
            }
            if (!empty($quotedSelectedIds)) {
                //add a condition if id numbers are specified
                $conditionFilters[] = 'id IN (' . implode(',', $quotedSelectedIds) . ')';
            }
            $conditionFilters[] = 'path LIKE ' . $db->quote(Helper::escapeLike($parentPath) . '/%') . ' AND type != ' . $db->quote('folder');
            if (!$this->getAdminUser()->isAdmin()) {
                $userIds = $this->getAdminUser()->getRoles();
                $userIds[] = $this->getAdminUser()->getId();
                $conditionFilters[] = ' (
                                                    (select list from users_workspaces_asset where userId in (' . implode(',', $userIds) . ') and LOCATE(CONCAT(path, filename),cpath)=1  ORDER BY LENGTH(cpath) DESC LIMIT 1)=1
                                                    OR
                                                    (select list from users_workspaces_asset where userId in (' . implode(',', $userIds) . ') and LOCATE(cpath,CONCAT(path, filename))=1  ORDER BY LENGTH(cpath) DESC LIMIT 1)=1
                                                 )';
            }

            $condition = implode(' AND ', $conditionFilters);

            $assetList = new Asset\Listing();
            $assetList->setCondition($condition);
            $assetList->setOrderKey('LENGTH(path)', false);
            $assetList->setOrder('ASC');

            for ($i = 0; $i < ceil($assetList->getTotalCount() / $filesPerJob); $i++) {
                $jobs[] = [[
                    'url' => $this->generateUrl('pimcore_admin_asset_downloadaszipaddfiles'),
                    'method' => 'GET',
                    'params' => [
                        'id' => $asset->getId(),
                        'selectedIds' => implode(',', $selectedIds),
                        'offset' => $i * $filesPerJob,
                        'limit' => $filesPerJob,
                        'jobId' => $jobId,
                    ],
                ]];
            }
        }

        return $this->adminJson([
            'success' => true,
            'jobs' => $jobs,
            'jobId' => $jobId,
        ]);
    }
