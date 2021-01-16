<?php

class EntityCrud extends Base{
    private $params = [];
    
    public function loadParams(array $params) {
        $this->params = $params;
    }

    protected function loadRecords() {
        $json_api = new JsonlFileApi($this->config);
        $this->instantiateEntity("Entity", NULL);
        return array_map(
            function ($record) {
                return $this->instantiateEntity($this->entity_name, $record);
            },
            $json_api->readEntity($this->entity_name)
        );
    }

    protected function saveRecords(array $records) {
        $json_api = new JsonlFileApi($this->config);
        $records = array_map(
            function($record) {
                return $record->dump();
            },
            $records
        );
        return $json_api->writeEntity($this->entity_name, $records);
    }

    private function instantiateEntity($entity_name, $content) {
        $entity_class = $this->getEntityClassFilename($entity_name);
        if (file_exists($this->pathMe() . $entity_class)) {
            require_once $this->pathMe() . $entity_class;
            $instance = new $entity_name();
            if ($content) $instance->load($content);
            return $instance;
        } else {
            throw new RuntimeException("Class for entity " . $entity_name . " not found.");
        }
    }

    private function getEntityClassFilename($entity_name) {
        return sprintf("%s" . DIRECTORY_SEPARATOR . "%s.php",
            $this->config->getParam("entities_path", "entities"),
            $entity_name
        );
    }

}