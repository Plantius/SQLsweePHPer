    public function fill_in_additional_detail_fields()
    {
        global $current_language, $timedate, $locale, $sugar_config;

        parent::fill_in_additional_detail_fields();

        $mod_strings = return_module_language($current_language, 'Documents');

        if (!empty($this->document_revision_id)) {
            $query = "SELECT users.first_name AS first_name, users.last_name AS last_name, document_revisions.date_entered AS rev_date,
            	 document_revisions.filename AS filename, document_revisions.revision AS revision,
            	 document_revisions.file_ext AS file_ext, document_revisions.file_mime_type AS file_mime_type
            	 FROM users, document_revisions
            	 WHERE users.id = document_revisions.created_by AND document_revisions.id = '$this->document_revision_id'";

            $result = $this->db->query($query);
            $row = $this->db->fetchByAssoc($result);

            //populate name
            if (isset($this->document_name)) {
                $this->name = $this->document_name;
            }

            if (isset($row['filename'])) {
                $this->filename = $row['filename'];
            }
            //$this->latest_revision = $row['revision'];
            if (isset($row['revision'])) {
                $this->revision = $row['revision'];
            }

            //image is selected based on the extension name <ext>_icon_inline, extension is stored in document_revisions.
            //if file is not found then default image file will be used.
            global $img_name, $img_name_bare;

            if (!empty($row['file_ext'])) {
                $img_name = SugarThemeRegistry::current()->getImageURL(strtolower($row['file_ext']) . "_image_inline.gif");
                $img_name_bare = strtolower($row['file_ext']) . "_image_inline";

                $allowedPreview = $sugar_config['allowed_preview'] ?? [];

                if (in_array($row['file_ext'], $allowedPreview, true)) {
                    $this->show_preview = true;
                }

            }
        }

        //set default file name.
        if (!empty($img_name) && file_exists($img_name)) {
            $img_name = $img_name_bare;
        } else {
            $img_name = "def_image_inline"; //todo change the default image.
        }
        if ($this->ACLAccess('DetailView')) {
            if (!empty($this->doc_type) && $this->doc_type != 'Sugar' && !empty($this->doc_url)) {
                $file_url = "<a href='" . $this->doc_url . "' target='_blank'>" . SugarThemeRegistry::current()->getImage(
                    $this->doc_type . '_image_inline',
                    'border="0"',
                    null,
                    null,
                    '.png',
                    $mod_strings['LBL_LIST_VIEW_DOCUMENT']
                ) . "</a>";
            } else {
                $file_url = "<a href='index.php?entryPoint=download&id={$this->document_revision_id}&type=Documents' target='_blank'>" . SugarThemeRegistry::current()->getImage(
                    $img_name,
                    'border="0"',
                    null,
                    null,
                    '.gif',
                    $mod_strings['LBL_LIST_VIEW_DOCUMENT']
                ) . "</a>";
            }

            $this->file_url = $file_url;
            $this->file_url_noimage = "index.php?entryPoint=download&type=Documents&id={$this->document_revision_id}";
        } else {
            $this->file_url = "";
            $this->file_url_noimage = "";
        }

        //get last_rev_by user name.
        if (!empty($row)) {
            $this->last_rev_created_name = $locale->getLocaleFormattedName($row['first_name'], $row['last_name']);

            $this->last_rev_create_date = $timedate->to_display_date_time($this->db->fromConvert(
                $row['rev_date'],
                'datetime'
            ));
            $this->last_rev_mime_type = $row['file_mime_type'];
        }

        global $app_list_strings;
        if (!empty($this->status_id)) {
            $this->status = $app_list_strings['document_status_dom'][$this->status_id];
        }
        if (!empty($this->related_doc_id)) {
            $this->related_doc_name = (new Document())->get_document_name($this->related_doc_id);
            $this->related_doc_rev_number = (new DocumentRevision)->get_document_revision_name($this->related_doc_rev_id);
        }
    }
