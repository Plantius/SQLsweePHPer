    protected function initCreationForms($a_new_type)
    {
        $forms = array();

        if ($this->id_type == self::WORKSPACE_NODE_ID) {
            if (!ilDiskQuotaHandler::isUploadPossible()) {
                $this->lng->loadLanguageModule("file");
                ilUtil::sendFailure($this->lng->txt("personal_workspace_quota_exceeded_warning"), true);
                $this->ctrl->redirect($this, "cancel");
            }
        }

        // use drag-and-drop upload if configured
        if (ilFileUploadSettings::isDragAndDropUploadEnabled()) {
            $forms[] = $this->initMultiUploadForm();
        } else {
            $forms[] = $this->initSingleUploadForm();
            $forms[] = $this->initZipUploadForm();
        }

        // repository only
        if ($this->id_type != self::WORKSPACE_NODE_ID) {
            $forms[self::CFORM_IMPORT] = $this->initImportForm('file');
            $forms[self::CFORM_CLONE] = $this->fillCloneTemplate(null, "file");
        }

        return $forms;
    }
