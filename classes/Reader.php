<?php

class Reader extends Base{

    private $now;
    private $latest;
    private $entities;

    public function __construct(Config $config) {
        $this->now = date("Y-m-d");
        $this->config = $config;
        $this->db_path_template = $config->getParam("db_path", "%s_%s.jsonl");
        $this->slug_latest = $config->getParam("slug_latest", "latest");
        $this->entities_path = $config->getParam("entities_path", "entities");

        $this->instantiateEntity("Entity", NULL);
    }

    public function getEntities() {
        $dbs = [];
        foreach (array_keys($this->config->getEntities()) as $entity_name) {
            $filename = $this->getEntityDbLatestFilename($entity_name);
            if (file_exists($filename)) {
                $dbs[$entity_name] = [];
                foreach ($this->readJsonlFile($filename) as $record) {
                    $dbs[$entity_name][] = $this->instantiateEntity($entity_name, $record);
                }
            } else {
                $dbs[$entity_name] = [];
            }
        }
        return $dbs;
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
            $this->entities_path,
            $entity_name
        );
    }

    private function getEntityDbLatestFilename($entity_name) {
        return sprintf($this->pathMe() . $this->db_path_template,
            $this->config->getEntity($entity_name)["db_slug"],
            $this->slug_latest
        );
    }

    private function readJsonlFile($filename) {
        $json_lines = explode("\n", file_get_contents($filename));
        return array_map(
            function ($content) {
                return json_decode($content, true);
            },
            $json_lines
        );
    }
}