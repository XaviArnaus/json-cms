<?php

class Entity {
    public function load(array $params) {
        foreach ($params as $param => $value) {
            if (property_exists($this, $param)) {
                $this->$param = $value;
            }
        }
    }

    public function dump() {
        // This only should give the public properties only, even from $this.
        return call_user_func('get_object_vars', $this);
    }
}