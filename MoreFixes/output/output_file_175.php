function html_input($type = 'text', $name = '', $value = '', $attributes = []) {

    if ($type === 'password') {
        $attributes['autocomplete'] = 'off';
    }
    $attributes['type']  = $type;
    $attributes['name']  = $name;
    $attributes['value'] = $value;

    return html_tag_short('input', $attributes, 'input form-control');
}
