    protected function handleFileUpload($file_upload)
    {
        global $DIC;
        $ilUser = $DIC['ilUser'];

        if ($DIC->upload()->hasBeenProcessed() !== true) {
            if (PATH_TO_GHOSTSCRIPT !== "") {
                $DIC->upload()->register(new ilCountPDFPagesPreProcessors());
            }
        }

        $DIC->upload()->process();
        /**
         * @var $item \ILIAS\FileUpload\DTO\UploadResult
         */
        $item = reset($DIC->upload()->getResults());

        // file upload params

        $file_upload['name'] = $item->getName();

        $filename = ilUtil::stripSlashes($item->getName());
        $type = ilUtil::stripSlashes($item->getMimeType());
        $size = ilUtil::stripSlashes($item->getSize());
        $temp_name = $item->getPath(); // currently used

        // additional params
        $title = ilUtil::stripSlashes($file_upload["title"]);
        $description = ilUtil::stripSlashes($file_upload["description"]);
        $extract = ilUtil::stripSlashes($file_upload["extract"]);
        $keep_structure = ilUtil::stripSlashes($file_upload["keep_structure"]);

        // create answer object
        $response = new stdClass();
        $response->fileName = $filename;
        $response->fileSize = intval($size);
        $response->fileType = $type;
        $response->fileUnzipped = $extract;
        $response->error = null;

        // extract archive?
        if ($extract) {
            $zip_file = $filename;
            $adopt_structure = $keep_structure;

            include_once("Services/Utilities/classes/class.ilFileUtils.php");

            // Create unzip-directory
            $newDir = ilUtil::ilTempnam();
            ilUtil::makeDir($newDir);

            // Check if permission is granted for creation of object, if necessary
            if ($this->id_type != self::WORKSPACE_NODE_ID) {
                $type = ilObject::_lookupType((int) $this->parent_id, true);
            } else {
                $type = ilObject::_lookupType($this->tree->lookupObjectId($this->parent_id), false);
            }

            $tree = $access_handler = null;
            switch ($type) {
                // workspace structure
                case 'wfld':
                case 'wsrt':
                    $permission = $this->checkPermissionBool("create", "", "wfld");
                    $containerType = "WorkspaceFolder";
                    $tree = $this->tree;
                    $access_handler = $this->getAccessHandler();
                    break;

                // use categories as structure
                case 'cat':
                case 'root':
                    $permission = $this->checkPermissionBool("create", "", "cat");
                    $containerType = "Category";
                    break;

                // use folders as structure (in courses)
                default:
                    $permission = $this->checkPermissionBool("create", "", "fold");
                    $containerType = "Folder";
                    break;
            }

            try {
                // 	processZipFile (
                //		Dir to unzip,
                //		Path to uploaded file,
                //		should a structure be created (+ permission check)?
                //		ref_id of parent
                //		object that contains files (folder or category)
                //		should sendInfo be persistent?)
                ilFileUtils::processZipFile(
                    $newDir,
                    $temp_name,
                    ($adopt_structure && $permission),
                    $this->parent_id,
                    $containerType,
                    $tree,
                    $access_handler
                );
            } catch (ilFileUtilsException $e) {
                $response->error = $e->getMessage();
            } catch (Exception $ex) {
                $response->error = $ex->getMessage();
            }

            ilUtil::delDir($newDir);

            // #15404
            if ($this->id_type != self::WORKSPACE_NODE_ID) {
                foreach (ilFileUtils::getNewObjects() as $parent_ref_id => $objects) {
                    if ($parent_ref_id != $this->parent_id) {
                        continue;
                    }

                    foreach ($objects as $object) {
                        $this->after_creation_callback_objects[] = $object;
                    }
                }
            }
        } else {
            // create and insert file in grp_tree
            $fileObj = new ilObjFile();
            // bugfix mantis 0026043
            if (strlen(trim($title)) == 0) {
                $title = $filename;
            } else {
                $title = $fileObj->checkFileExtension($filename, $title);
            }
            $fileObj->setTitle($title);
            $fileObj->setDescription($description);
            $fileObj->setFileName($filename);
            $fileObj->setFileType($type);
            $fileObj->setFileSize($size);
            $this->object_id = $fileObj->create();
            $this->putObjectInTree($fileObj, $this->parent_id);

            // see uploadFiles()
            if (is_array($this->after_creation_callback_objects)) {
                $this->after_creation_callback_objects[] = $fileObj;
            }

            // upload file to filesystem
            $fileObj->createDirectory();
            $fileObj->raiseUploadError(true);

            $result = $fileObj->getUploadFile($temp_name, $filename);

            if ($result) {
                //if no title for the file was set use the filename as title
                if (empty($fileObj->getTitle())) {
                    $fileObj->setTitle($filename);
                }
                $fileObj->setFileName($filename);
            }
            $fileObj->update();
            $this->handleAutoRating($fileObj);

            ilChangeEvent::_recordWriteEvent($fileObj->getId(), $ilUser->getId(), 'create');
        }

        return $response;
    }
