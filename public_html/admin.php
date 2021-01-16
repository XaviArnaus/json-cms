<?php
require_once "../classes/Base.php";
require_once "../classes/Config.php";
require_once "../classes/Reader.php";
require_once "../classes/Crud.php";
require_once "../classes/JsonlFileApi.php";

class Admin {
	const CONFIG_FILE = "../config.json";
    private $config;
    private $reader;
    private $crud;

    public function __construct() {
        $this->config = new Config(self::CONFIG_FILE);
        $this->reader = new Reader($this->config);
        $this->crud = new Crud($this->config);
    }

    public function doRequest() {
      switch ($_GET["section"]) {
        case "crud":
          $this->do([
            "entity" => $_GET["entity"],
            "action" => $_GET["action"]
          ]);
          break;
      }
    }

    public function run() {
      print "Hello Admin World";
      print "<pre>";
      var_dump($this->config);
      print "</pre>";
    }

    public function printMeta($param) {
      print $this->config->getParam("layout")["metadata"][$param];
    }

    public function getRecords() {
      return $this->reader->getEntities();

    }

    public function getConfigParam($param) {
      return $this->config->getParam($param);
    }
}

$app = new Admin();
// $app->run();
?>

<html>
  <head>

  </head>
  <body>
    <h1><? $app->printMeta("title") ?> Admin</h1>
    <? foreach ($app->getRecords() as $entity_name => $records) {?>
      <strong><?= ucfirst($app->getConfigParam("entities")[$entity_name]["collection"]); ?>:</strong>
      <ul>
        <li><a href="?section=crud&entity=<?=$entity_name?>&action=new">New <?=$entity_name?></a></li>
        <? foreach ($records as $record) {?>
          <li><?= var_dump($record)?></li>
        <? } ?>
    </ul>
    <? } ?>
  </body>
</html>