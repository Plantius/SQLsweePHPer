                ilObject2GUI::handleAfterSaveCallback($new_file_obj, $after_creation_callback);
            }
            unset($this->after_creation_callback_objects);
        }

        // send response object (don't use 'application/json' as IE wants to download it!)
        header('Vary: Accept');
        header('Content-type: text/plain');

        if ($DIC->upload()->hasBeenProcessed()) {
            foreach ($DIC->upload()->getResults() as $result) {
                if (!ilFileUtils::hasValidExtension($result->getName())) {
                    $this->lng->loadLanguageModule('file');
                    ilUtil::sendInfo(
                        $this->lng->txt('file_upload_info_file_with_critical_unknown_extension_later_renamed_when_downloading'),
                        true
                    );
                }
            }
        }
        echo json_encode($response);
        // no further processing!
        exit;
    }
