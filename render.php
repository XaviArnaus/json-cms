<?php
require_once "classes/Config.php";
require_once "classes/Reader.php";
require_once "classes/Render.php";
require_once "classes/StaticWriter.php";


class App {
    const CONFIG_FILE = "config.json";
    private $config;
    private $reader;
    private $render;
    private $static_writer;

    public function __construct() {
        $this->config = new Config(self::CONFIG_FILE);
        $this->reader = new Reader($this->config);
        $this->render = new Render($this->config);
        $this->static_writer = new StaticWriter($this->config);
    }

    public function run() {
        $records = $this->reader->getEntities();
        $this->render->setEntitiesToRender($records);
        $content = $this->render->render("home");
        $this->static_writer->write("home", $content);
    }
}

$app = new App();
$app->run();