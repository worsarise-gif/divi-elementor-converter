<?php
namespace SuiteEdge\Migrator\Model;
class AssetReference {
    public $url; public $id; public $alt;
    public function __construct($url,$id = 0,$alt = '') { $this->url = (string)$url; $this->id = (int)$id; $this->alt = (string)$alt; }
    public function to_array() { return ['url'=>$this->url,'id'=>$this->id,'alt'=>$this->alt]; }
}
