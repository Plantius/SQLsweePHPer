                $pfTaskJob->update($input);
                $tasks_id = $data['plugin_glpiinventory_tasks_id'];
            }
        } else {
           // case 2: if not exist, create a new task + taskjob
            $this->getFromDB($packages_id);

           //Add the new task
            $input = [
            'name'                    => '[deploy on demand] ' . $this->fields['name'],
            'entities_id'             => $computer->fields['entities_id'],
            'reprepare_if_successful' => 0,
            'is_deploy_on_demand'     => 1,
            'is_active'               => 1,
            ];
            $tasks_id = $pfTask->add($input);

           //Add a new job for the newly created task
           //and enable it
            $input = [
            'plugin_glpiinventory_tasks_id' => $tasks_id,
            'entities_id' => $computer->fields['entities_id'],
            'name'        => 'deploy',
            'method'      => 'deployinstall',
            'targets'     => '[{"PluginGlpiinventoryDeployPackage":"' . $packages_id . '"}]',
            'actors'      => exportArrayToDB([['Computer' => $computers_id]]),
            'enduser'     => exportArrayToDB([$users_id  => [$computers_id]]),
            ];
            $pfTaskJob->add($input);
        }

       //Prepare the task (and only this one)
        $pfTask->prepareTaskjobs(['deployinstall'], $tasks_id);
    }
