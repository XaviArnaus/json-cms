<?php

class Reader extends Base{

    private $now;
    private $config;
    private $db_path_template;
    private $slug_latest;
    private $entities_path;

    public function __construct(Config $config) {
        $this->now = date("Y-m-d");
        $this->config = $config;
        $this->db_path_template = $config->getParam("db_path", "%s_%s.jsonl");
        $this->slug_latest = $config->getParam("slug_latest", "latest");
        $this->entities_path = $config->getParam("entities_path", "entities");
    }

    public function getEntities() {
        $dbs = [];
        foreach (array_keys($this->config->getEntities()) as $entity_name) {
            $entity_crud = $this->instantiateEntityCrud($entity_name);
            $dbs[$entity_name] = $entity_crud->loadAll();
        }
        return $dbs;
    }

    private function instantiateEntityCrud($entity_name) {
        $entity_crud_class = $this->getEntityCrudClassFilename($entity_name);
        if (file_exists($this->pathMe() . $entity_crud_class)) {
            require_once $this->pathMe() . $this->getEntityCrudClassFilename("Entity");
            require_once $this->pathMe() . $entity_crud_class;
            $class_name = $entity_name . "Crud";
            $instance = new $class_name($this->config);
            return $instance;
        } else {
            throw new RuntimeException("Crud class for entity " . $entity_name . " not found.");
        }
    }

    private function getEntityCrudClassFilename($entity_name) {
        return sprintf("%s" . DIRECTORY_SEPARATOR . "%sCrud.php",
            $this->entities_path,
            $entity_name
        );
    }
}