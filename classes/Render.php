<?php

class Render extends Base{

    const LISTED_PREFIX = "listed_";

    private $now;
    private $entities;
    private $templates;
    private $entities_templates = [];
    private $sections_templates = [];
    private $choosen_template;
    private $config;
    private $loaded_templates = [];

    public function __construct(Config $config) {
        $this->now = date("Y-m-d-H-i-s");
        $this->choosen_template = $config->getParam("display_template", "default");
        $this->config = $config;
        $this->templates = [
            "styles" => $this->pathMe() . "templates/" . $this->choosen_template . "/styles.css",
            "layout" => $this->pathMe() . "templates/" . $this->choosen_template . "/layout.html",
            "css_item" => $this->pathMe() . "templates/" . $this->choosen_template . "/css_item.html",
            "js_item" => $this->pathMe() . "templates/" . $this->choosen_template . "/js_item.html",
            "navigation_item" => $this->pathMe() . "templates/" . $this->choosen_template . "/navigation_item.html",
        ];

        $this->buildEntitiesTemplates();
        $this->buildSectionsTemplates();
    }

    public function setEntitiesToRender(array $entities) {
        $this->entities = $entities;
    }

    public function render($profile) {
        $render_profile = $this->config->getRenderProfile($profile);

        $records = $this->applyGrouping($render_profile["grouping"], $this->entities);
        $records = $this->applySort($render_profile["sorting"], $records);

        $section = "";

        foreach ($records as $collection_name => $record_list) {
            $record_list = array_map(
                function ($record) {
                    return $this->renderEntity($record);
                },
                $record_list
            );

            // This is wrong. A profile is like "home", a section is a list of records inside a profile.
            // A profile should be able to hold several sections.
            $section_content = $this->getSectionTemplateContent($profile);
            $section = $this->renderSection($collection_name, $record_list, $section_content);
        }

        $content = $this->renderLayout([
            "section" => $section
        ]);

        return $content;
    }

    private function applyGrouping($grouping_method, $records) {
        // "entity": array of records inside an array of entities. This is by default.
        // "feed": array of records (all entities merged) inside a 1 position array "feed"

        // NOTE: Well, would make sense not to reorganize, maybe just to add new groupings,
        //  So we can have severall lists in the same page (Home: Feed + last images in sidebar).

        switch ($grouping_method) {
            case "feed":
                $new_grouping = ["feed" => []];
                foreach ($records as $current_grouping => $record_list) {
                    foreach ($record_list as $record) {
                        $new_grouping["feed"][] = $record;
                    }
                }
                return $new_grouping;
            case "entity":
            default:
                return $records;
        }
    }

    private function applySort($sorting_method, $records) {
        // "date": per creation date, descend. This is by default.

        return $records;
    }

    private function buildEntitiesTemplates() {
        foreach ($this->config->getEntities() as $entity_name => $params) {
            $this->entities_templates[$entity_name] = $this->pathMe() . "templates" . DIRECTORY_SEPARATOR .
                $this->choosen_template . DIRECTORY_SEPARATOR .
                sprintf("%s.html", self::LISTED_PREFIX . $params["template"]);
        }
    }

    private function buildSectionsTemplates() {
        foreach ($this->config->getRenderProfiles() as $render_profile => $params) {
            $this->sections_templates[$render_profile] = $this->pathMe() . "templates" . DIRECTORY_SEPARATOR .
                $this->choosen_template . DIRECTORY_SEPARATOR .
                sprintf("%s.html", $params["template"]);
        }
    }

    private function renderSection($collection_name, $collection, $content) {
        $collection_name = "{%-" . strtoupper($collection_name) . "-%}";
        return str_replace($collection_name, implode("/n",$collection), $content);
    }

    private function renderEntity($record) {
        $content = $this->getEntityTemplateContent($record->entity_name);
        $fields_to_replace = get_object_vars($record);
        foreach ($fields_to_replace as $field => $value) {
            $field = "{%-" . strtoupper($field) . "-%}";
            $content = str_replace($field, $value, $content);
        }
        return $content;
    }

    private function renderTemplate($template_name, $parameters = []) {
        $content = $this->getTemplateContent($template_name);
        foreach ($parameters as $key => $value) {
            $content = str_replace($key,$value,$content);
        }
        return $content;
    }

    private function getTemplateContent($template_name) {
        if (!isset($this->loaded_templates[$template_name]))
            $this->loaded_templates[$template_name] = file_get_contents($this->templates[$template_name]);
        
        return $this->loaded_templates[$template_name];
    }

    private function getEntityTemplateContent($template_name) {
        if (!isset($this->loaded_templates[$template_name]))
            $this->loaded_templates[$template_name] = file_get_contents($this->entities_templates[$template_name]);
        
        return $this->loaded_templates[$template_name];
    }

    private function getSectionTemplateContent($template_name) {
        if (!isset($this->loaded_templates[$template_name]))
            $this->loaded_templates[$template_name] = file_get_contents($this->sections_templates[$template_name]);
        
        return $this->loaded_templates[$template_name];
    }

    private function renderLayout(array $params) {
        $metadata = $this->config->getParam("layout")["metadata"];

        return $this->renderTemplate(
            "layout",
            [
                "{%-TITLE-%}" => $metadata["title"],
                "{%-SITE_URL-%}" => $metadata["site_url"],
                "{%-DESCRIPTION-%}" => $metadata["description"],
                "{%-FAVICON-%}" => $metadata["favicon"],
                "{%-CSS-%}" => $this->renderCSS(),
                "{%-JS-%}" => $this->renderJS(),
                "{%-NAVIGATION-%}" => $this->renderNavigation(),
                "{%-CONTENT-%}" => $params["section"]
            ]
        );
    }

    private function renderNavigation() {
        return implode("\n",array_map(
            function($render_profile) {
                return $this->renderTemplate("navigation_item", [
                    "{%-URL-%}" => $render_profile["slug"] . StaticWriter::OUTPUT_EXTENSION,
                    "{%-TITLE-%}" => ucfirst($render_profile["name"])
                ]);
            },
            $this->config->getRenderProfiles()
        ));
    }

    private function renderCSS() {
        return implode("\n",array_map(
            function($css_item) {
                return $this->renderTemplate("css_item", [
                    "{%-CSS-%}" => $css_item . "?version=" . $this->now
                ]);
            },
            $this->config->getParam("layout")["metadata"]["css"]
        ));
    }

    private function renderJS() {
        return implode("\n",array_map(
            function($css_item) {
                return $this->renderTemplate("js_item", [
                    "{%-JS-%}" => $css_item . "?version=" . $this->now
                ]);
            },
            $this->config->getParam("layout")["metadata"]["js"]
        ));
    }
}