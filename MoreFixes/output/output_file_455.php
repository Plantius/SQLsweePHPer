    public function infoScreenForward()
    {
        global $DIC;
        $ilTabs = $DIC['ilTabs'];
        $ilErr = $DIC['ilErr'];
        $ilToolbar = $DIC['ilToolbar'];

        $ilTabs->activateTab("id_info");

        if (!$this->checkPermissionBool("visible") && !$this->checkPermissionBool("read")) {
            $ilErr->raiseError($this->lng->txt("msg_no_perm_read"));
        }

        include_once("./Services/InfoScreen/classes/class.ilInfoScreenGUI.php");
        $info = new ilInfoScreenGUI($this);

        if ($this->checkPermissionBool("read", "sendfile")) {
            // #9876
            $this->lng->loadLanguageModule("file");

            // #14378
            include_once "Services/UIComponent/Button/classes/class.ilLinkButton.php";
            $button = ilLinkButton::getInstance();
            $button->setCaption("file_download");
            $button->setPrimary(true);

            // get permanent download link for repository
            if ($this->id_type == self::REPOSITORY_NODE_ID) {
                $button->setUrl(ilObjFileAccess::_getPermanentDownloadLink($this->node_id));
            } else {
                $button->setUrl($this->ctrl->getLinkTarget($this, "sendfile"));
            }

            $ilToolbar->addButtonInstance($button);
        }

        $info->enablePrivateNotes();

        if ($this->checkPermissionBool("read")) {
            $info->enableNews();
        }

        // no news editing for files, just notifications
        $info->enableNewsEditing(false);
        if ($this->checkPermissionBool("write")) {
            $news_set = new ilSetting("news");
            $enable_internal_rss = $news_set->get("enable_rss_for_internal");

            if ($enable_internal_rss) {
                $info->setBlockProperty("news", "settings", true);
                $info->setBlockProperty("news", "public_notifications_option", true);
            }
        }

        // standard meta data
        $info->addMetaDataSections($this->object->getId(), 0, $this->object->getType());

        // File Info
        $info->addSection($this->lng->txt("file_info"));
        $info->addProperty($this->lng->txt("filename"), $this->object->getFileName());
        $info->addProperty($this->lng->txt("type"), $this->object->guessFileType());

        $info->addProperty($this->lng->txt("size"), ilUtil::formatSize(ilObjFile::_lookupFileSize($this->object->getId()), 'long'));
        $info->addProperty($this->lng->txt("version"), $this->object->getVersion());

        if ($this->object->getPageCount() > 0) {
            $info->addProperty($this->lng->txt("page_count"), $this->object->getPageCount());
        }

        // using getVersions function instead of ilHistory direct
        $uploader = $this->object->getVersions();
        $uploader = array_shift($uploader);
        $uploader = $uploader["user_id"];

        $this->lng->loadLanguageModule("file");
        include_once "Services/User/classes/class.ilUserUtil.php";
        $info->addProperty($this->lng->txt("file_uploaded_by"), ilUserUtil::getNamePresentation($uploader));

        // download link added in repository
        if ($this->id_type == self::REPOSITORY_NODE_ID && $this->checkPermissionBool("read", "sendfile")) {
            $tpl = new ilTemplate("tpl.download_link.html", true, true, "Modules/File");
            $tpl->setVariable("LINK", ilObjFileAccess::_getPermanentDownloadLink($this->node_id));
            $info->addProperty($this->lng->txt("download_link"), $tpl->get());
        }

        if ($this->id_type == self::WORKSPACE_NODE_ID) {
            $info->addProperty($this->lng->txt("perma_link"), $this->getPermanentLinkWidget());
        }

        // display previews
        include_once("./Services/Preview/classes/class.ilPreview.php");
        if (!$this->ctrl->isAsynch()
            && ilPreview::hasPreview($this->object->getId(), $this->object->getType())
            && $this->checkPermissionBool("read")
        ) {
            include_once("./Services/Preview/classes/class.ilPreviewGUI.php");

            // get context for access checks later on
            $context;
            switch ($this->id_type) {
                case self::WORKSPACE_NODE_ID:
                case self::WORKSPACE_OBJECT_ID:
                    $context = ilPreviewGUI::CONTEXT_WORKSPACE;
                    break;

                default:
                    $context = ilPreviewGUI::CONTEXT_REPOSITORY;
                    break;
            }

            $preview = new ilPreviewGUI($this->node_id, $context, $this->object->getId(), $this->access_handler);
            $info->addProperty($this->lng->txt("preview"), $preview->getInlineHTML());
        }

        // forward the command
        // $this->ctrl->setCmd("showSummary");
        // $this->ctrl->setCmdClass("ilinfoscreengui");
        $this->ctrl->forwardCommand($info);
    }
