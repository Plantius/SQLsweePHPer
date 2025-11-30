    public function store(Request $request, FlasherInterface $flasher)
    {
        $this->validate($request, [
            'name' => 'required|string|unique:device_groups',
            'type' => 'required|in:dynamic,static',
            'devices' => 'array|required_if:type,static',
            'devices.*' => 'integer',
            'rules' => 'json|required_if:type,dynamic',
        ]);

        $deviceGroup = DeviceGroup::make($request->only(['name', 'desc', 'type']));
        $deviceGroup->rules = json_decode($request->rules);
        $deviceGroup->save();

        if ($request->type == 'static') {
            $deviceGroup->devices()->sync($request->devices);
        }

        $flasher->addSuccess(__('Device Group :name created', ['name' => $deviceGroup->name]));

        return redirect()->route('device-groups.index');
    }
