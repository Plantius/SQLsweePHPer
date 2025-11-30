                $item['controller_title'] = string_lang($item['target_controller'].'_CONTROLLER');

                $item['subject_title'] = $item['controller_title'];

                if($subj_controller !== null){

                    $ctype = $subj_controller->getContentTypeForModeration($item['target_subject']);

                    $item['subject_title'] = $ctype['title'];

                }

                return $item;

            });

            $this->cms_template->renderGridRowsJSON($grid, $data, $total, $pages);

            $this->halt();

        }

        if($additional_h1){
            $this->cms_template->setPageH1($additional_h1);
        }

        $this->model->resetFilters();

		return $this->cms_template->render('backend/logs', array(
            'grid'      => $grid,
            'sub_url'   => $sub_url,
            'url_query' => $url_query,
            'url'       => $url.($sub_url ? '/'.implode('/', $sub_url) : '').(($action > -1) ? '?'.http_build_query($url_query) : '')
        ));
