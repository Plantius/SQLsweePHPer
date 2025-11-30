    private function manageQuantitiesAndProcurement($id, $to_status, $current)
    {
        if (($to_status == 0 || $to_status == 2) && $current == 1) {
            $operator = '+';
            $operator_pro = '-';
        }
        if ($to_status == 1) {
            $operator = '-';
            $operator_pro = '+';
        }
        $this->db->select('products');
        $this->db->where('id', $id);
        $result = $this->db->get('orders');
        $arr = $result->row_array();
        $products = unserialize($arr['products']);
        foreach ($products as $product) {
                if (isset($operator)) {
                    if (!$this->db->query('UPDATE products SET quantity=quantity' . $operator . $product['product_quantity'] . ' WHERE id = ' . $product['product_info']['id'])) {
                        log_message('error', print_r($this->db->error(), true));
                        show_error(lang('database_error'));
                    }
                }
                if (isset($operator_pro)) {
                    if (!$this->db->query('UPDATE products SET procurement=procurement' . $operator_pro . $product['product_quantity'] . ' WHERE id = ' . $product['product_info']['id'])) {
                        log_message('error', print_r($this->db->error(), true));
                        show_error(lang('database_error'));
                    }
                } 
        }
    }
