<?php
class PaginationQueryDto{
    public $skip = 0;
    public $take = 5;
    public $sort;
    public $searchQuery;
    public $role;
    function __construct($obj) {
        $this->skip = $obj->skip;
        $this->take = $obj->take;


    }
}
?>