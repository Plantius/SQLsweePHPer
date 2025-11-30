    public function store()
    {
        $title = $this->title;
        if ($title === null || trim($title) === '') {
            $title = new Expression('NULL');
        }

        $subtitle = $this->subtitle;
        if ($subtitle === null || trim($subtitle) === '') {
            $subtitle = new Expression('NULL');
        }

        $data = array(
            'model_header'      => $this->header,
            'model_footer'      => $this->footer,
            'model_type'        => $this->type,
            'model_title'       => $title,
            'model_subtitle'    => $subtitle,
            'model_body'        => $this->body,
            'model_styles'      => $this->styles
        );

        try {
            if ($this->id !== null) {
                $update = $this->zdb->update(self::TABLE);
                $update->set($data)->where(
                    self::PK . '=' . $this->id
                );
                $this->zdb->execute($update);
            } else {
                $data['model_name'] = $this->name;
                $insert = $this->zdb->insert(self::TABLE);
                $insert->values($data);
                $add = $this->zdb->execute($insert);
                if (!($add->count() > 0)) {
                    Analog::log('Not stored!', Analog::ERROR);
                    return false;
                }
            }
            return true;
        } catch (Throwable $e) {
            Analog::log(
                'An error occurred storing model: ' . $e->getMessage() .
                "\n" . print_r($data, true),
                Analog::ERROR
            );
            throw $e;
        }
    }
