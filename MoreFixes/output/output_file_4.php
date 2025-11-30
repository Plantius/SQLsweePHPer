            $controller_object = cmsCore::getController($controller_name);

            foreach ($hooks as $event_name) {

                $hook_class_name = 'on' . string_to_camel('_', $controller_name) . string_to_camel('_', $event_name);

                $hook_object = new $hook_class_name($controller_object);

                // Некоторые хуки не требуют регистрации в базе данных,
                // Например, хуки для CRON или иные, которые вызываются напрямую
                // Свойство $disallow_event_db_register в классе хука регулирует это поведение
                if(empty($hook_object->disallow_event_db_register)){

                    $events[$controller_name][$index] = $event_name;

                    $index++;
                }
            }
        }

        return $events;
    }
