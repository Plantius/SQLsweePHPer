                    $query->where('id', '!=', $deviceGroup->id);
                }),
            ],
            'type' => 'required|in:dynamic,static',
            'devices' => 'array|required_if:type,static',
            'devices.*' => 'integer',
            'rules' => 'json|required_if:type,dynamic',
        ]);

        $deviceGroup->fill($request->only(['name', 'desc', 'type']));

        $devices_updated = false;
        if ($deviceGroup->type == 'static') {
            // sync device_ids from input
            $updated = $deviceGroup->devices()->sync($request->get('devices', []));
            // check for attached/detached/updated
            $devices_updated = array_sum(array_map(function ($device_ids) {
                return count($device_ids);
            }, $updated)) > 0;
        } else {
            $deviceGroup->rules = json_decode($request->rules);
        }

        if ($deviceGroup->isDirty() || $devices_updated) {
            try {
                if ($deviceGroup->save() || $devices_updated) {
                    $flasher->addSuccess(__('Device Group :name updated', ['name' => $deviceGroup->name]));
                } else {
                    $flasher->addError(__('Failed to save'));

                    return redirect()->back()->withInput();
                }
            } catch (\Illuminate\Database\QueryException $e) {
                return redirect()->back()->withInput()->withErrors([
                    'rules' => __('Rules resulted in invalid query: ') . $e->getMessage(),
                ]);
            }
        } else {
            $flasher->addInfo(__('No changes made'));
        }

        return redirect()->route('device-groups.index');
    }
