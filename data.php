<?php

use maestroerror\dataRemastery;

class Data extends dataRemastery {
    public $id;

    // seo
    public $title;
    public $real_name;
    public $descr;
    public $keywords;
    public $slug;


    public $main_img;
    public $cover;
    public $player_img;
    public $story;
    public $date;

    // Relations
    public $author;

    public $tags;
    public $category;
}




