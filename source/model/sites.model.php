<?php
class sitesModel extends model
{
    public $base;
    public function __construct(&$base)
    {
        parent::__construct($base);
        $this->base = $base;
        $this->table = "sites";
    }
}
