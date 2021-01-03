<?php

class Config {
    private $config_file = "./config.json";
    private $config = [];

    public function __construct($config_file = null) {
        if (!is_null($config_file)) $this->config_file = $config_file;

        $this->load();
        $this->loadEntities();
        $this->loadRenderProfiles();
    }

    public function load() {
        try {
            $json_file = $this->readFile();
            $this->config = $this->getJson($json_file);
        } catch (Exception $e) {
            print "Impossible to read Config file at [" . realpath($this->config_file) . "]: " . $e->getMessage();
        }
    }

    public function getParam($param_name, $default = null) {
        if (isset($this->config[$param_name])) return $this->config[$param_name];
        elseif ($default != null) return $default;
        else throw new RuntimeException("Parameter " . $param_name . " not found");
    }

    public function getEntities() {
        return $this->config['entities'];
    }

    public function getEntity($entity_name) {
        if (isset($this->config["entities"][$entity_name])) return $this->config["entities"][$entity_name];
        else throw new RuntimeException("Entity " . $entity_name . " not found in the config");
    }

    private function loadEntities() {
        $entities = [];
        foreach ($this->config["entities"] as $entity) {
            $entities[$entity["name"]] = $entity;
        }
        $this->config["entities"] = $entities;
    }

    public function getRenderProfiles() {
        return $this->config['render_profiles'];
    }

    public function getRenderProfile($render_profile_name) {
        if (isset($this->config["render_profiles"][$render_profile_name]))
            return $this->config["render_profiles"][$render_profile_name];
        else
            throw new RuntimeException("Render Profile " . $render_profile_name . " not found in the config");
    }

    private function loadRenderProfiles() {
        $render_profiles = [];
        foreach ($this->config["render_profiles"] as $render_profile) {
            $render_profiles[$render_profile["name"]] = $render_profile;
        }
        $this->config["render_profiles"] = $render_profiles;
    }

    private function readFile() {
        return file_get_contents($this->config_file);
    }

    private function getJson($file_contents) {
        return json_decode($file_contents, true);
    }
}