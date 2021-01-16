<?php

class PostCrud extends EntityCrud{
    protected $entity_name = "Post";

    private $params = [];
    private $records = [];

    public function __construct(Config $config) {
        $this->config = $config;
        
        $this->records = $this->loadRecords();
    }
    
    public function loadParams(array $params) {
        $this->params = $params;
    }

    public function loadAll() {
        return $this->records;
    }

    public function loadOne(string $id) {
        $key_found = $this->findOne($id);
        return $this->records[$key_found];
    }

    public function saveOne(Post $record) {
        $key_found = $this->findOne($record["id"]);
        $this->records[$key_found]= $record;
        return $this->saveRecords($this->records);
    }

    private function findOne(string $id) {
        return array_keys(array_filter(
            $this->records,
            function ($record) use ($id){
                return $record["id"] == $id;
            }
            ))[0];
    }

}