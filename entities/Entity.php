<?php

class Entity {
    public function load(array $params) {
        foreach ($params as $param => $value) {
            if (property_exists($this, $param)) {
                $this->$param = $value;
            }
        }
    }
}