<?php

class JsonlFileApi extends Base{
    private $config;
    private $db_path_template;
    private $slug_text;

    public function __construct(Config $config) {
        $this->config = $config;
        $this->db_path_template = $config->getParam("db_path", "%s_%s.jsonl");
        $this->slug_latest = $config->getParam("slug_latest", "latest");
    }

    public function readEntity(string $entity_name) {
        $filename = $this->getEntityDbLatestFilename($entity_name);
        if (file_exists($filename)) {
            return $this->readJsonlFile($filename);
        } else {
            return [];
        }
    }

    public function writeEntity(string $entity_name, array $records) {
        $filename = $this->getEntityDbLatestFilename($entity_name);
        return $this->writeJsonlFile($filename);
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

    private function writeJsonlFile($filename, array $records) {
        $records = array_map(
            function ($content) {
                return json_encode($content, true);
            },
            $records
        );
        return file_put_contents($filename, implode("\n", $records));
    }
}