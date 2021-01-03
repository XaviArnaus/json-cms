<?php

class Post extends Entity {
    public $entity_name = __CLASS__;
    public $id;
    public $created_at;
    public $author;
    public $title;
    public $content;
}