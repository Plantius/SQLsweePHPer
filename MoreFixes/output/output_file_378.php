    public function downloadCsvAction(Request $request)
    {
        $this->checkPermission('reports');
        if ($exportFile = $request->get('exportFile')) {
            $exportFile = PIMCORE_SYSTEM_TEMP_DIRECTORY . '/' . basename($exportFile);
            $response = new BinaryFileResponse($exportFile);
            $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
            $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, 'export.csv');
            $response->deleteFileAfterSend(true);

            return $response;
        }

        throw new FileNotFoundException("File \"$exportFile\" not found!");
    }
