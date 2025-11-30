    public function destroy(DeviceGroup $deviceGroup)
    {
        if ($deviceGroup->serviceTemplates()->exists()) {
            $msg = __('Device Group :name still has Service Templates associated with it. Please remove or update the Service Template accordingly', ['name' => $deviceGroup->name]);

            return response($msg, 200);
        }
        $deviceGroup->delete();

        $msg = __('Device Group :name deleted', ['name' => htmlentities($deviceGroup->name)]);

        return response($msg, 200);
    }
