<?php

class Crud extends Base {

    private $now = "";
    private $config;
    private $params;

    public function __construct(Config $config)  {
        $this->config = $config;
        $this->now = date("Y-m-d");
    }

    public function do(array $params) {
        $entity = $this->config->getEntity($params['entity']);
        $entity_crud_instance = $this->instantiateEntityCrud($entity);

        switch ($params['action']) {
            case "create":
                break;
            case "update":
                break;
            case "delete":
                break;
        }

    }

    private function instantiateEntityCrud($entity_name) {
        $entity_crud_class = $this->getEntityClassFilename($entity_name);
        if (file_exists($this->pathMe() . $entity_crud_class)) {
            require_once $this->pathMe() . $entity_crud_class;
            $instance = new $entity_crud_class();
            return $instance;
        } else {
            throw new RuntimeException("Class for entity crud " . $entity_name . " (" . $entity_crud_class . ") not found.");
        }
    }

    private function getEntityCrudClassFilename($entity_name) {
        return sprintf("%sCrud" . DIRECTORY_SEPARATOR . "%s.php",
            $this->entities_path,
            $entity_name
        );
    }
    
}