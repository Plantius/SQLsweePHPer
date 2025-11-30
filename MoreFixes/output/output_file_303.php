    private function __saveCert($server, $id, $client = false, $delete = false)
    {
        if ($client) {
            $subm = 'submitted_client_cert';
            $attr = 'client_cert_file';
            $ins  = '_client';
        } else {
            $subm = 'submitted_cert';
            $attr = 'cert_file';
            $ins  = '';
        }
        if (!$delete) {
            $ext = '';
            App::uses('File', 'Utility');
            App::uses('Folder', 'Utility');
            App::uses('FileAccessTool', 'Tools');
            if (isset($server['Server'][$subm]['name'])) {
                if ($this->request->data['Server'][$subm]['size'] != 0) {
                    if (!$this->Server->checkFilename($server['Server'][$subm]['name'])) {
                        throw new Exception(__('Filename not allowed'));
                    }
                    $file = new File($server['Server'][$subm]['name']);
                    $ext = $file->ext();
                    if (!$server['Server'][$subm]['size'] > 0) {
                        $this->Flash->error(__('Incorrect extension or empty file.'));
                        $this->redirect(array('action' => 'index'));
                    }

                    // read pem file data
                    $pemData = FileAccessTool::readFromFile($server['Server'][$subm]['tmp_name'], $server['Server'][$subm]['size']);
                } else {
                    return true;
                }
            } else {
                $pemData = base64_decode($server['Server'][$subm]);
            }
            $destpath = APP . "files" . DS . "certs" . DS;
            $dir = new Folder(APP . "files" . DS . "certs", true);
            $pemfile = new File($destpath . $id . $ins . '.' . $ext);
            $result = $pemfile->write($pemData);
            $s = $this->Server->read(null, $id);
            $s['Server'][$attr] = $s['Server']['id'] . $ins . '.' . $ext;
            if ($result) {
                $this->Server->save($s);
            }
        } else {
            $s = $this->Server->read(null, $id);
            $s['Server'][$attr] = '';
            $this->Server->save($s);
        }
        return true;
    }
