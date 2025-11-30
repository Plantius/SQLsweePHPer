    private function _parseAdminCommand($message)
    {
        if (str_contains($message, '/clear')) {
            $this->getModel()->removeMessages();
            return true;
        }

        if (str_contains($message, '/online')) {
            $online = $this->getModel()->getOnline(false);
            $ipArr = array();
            foreach ($online as $item) {
                $ipArr[] = $item->ip;
            }

            $message = 'Online: ' . implode(", ", $ipArr);
            $this->getModel()->addMessage('Admin Command', $message, '0.0.0.0');
            return true;
        }
    }
