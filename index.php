<?php 

/* Example of usage */

// Data class extended from dataRemastery
require "src/dataRemastery.php";
require "data.php";

$exData = include_once("exampleData.php");
  
$data = [];

foreach ($exData as $onePost) {
    $obj = new Data($onePost);
    // binds slug to alt_name (it means that field "alt_name" from source is equal to field "slug" in new data collection)
    $obj->bind("slug", "alt_name")
    ->bind("author", "autor")
    ->bind("story", "full_story")

    /* xfields is string type data like this: 
    |title|Game of Thrones||language|English||author|George R. R. Martin| 
    so it needs first leve separation by || and second by |*/

    // set separator (first level), set separatorX (second level) and resolveX (2 level resolve) to resolve field "xfields" and set data in collection "xCollection"
    ->separator("||")->separatorX("|")->resolveX("xfields", "xCollection")

    // set separator and resolve filed "category", bind it to fileds "category" in new data
    ->separator(",")->resolve("category")->bindResolved("category")
    ->separator(",")->resolve("tags")->bindResolved("tags")
    ->separator(",")->resolve("keywords")->bindResolved("keywords")

    // binds from resolver, that means from resolved "xfields", which we saved in "xCollection"
    ->bindR("real_name", "name", "xCollection")
    ->bindR("title", "geoname", "xCollection")
    ->bindR("player_img", "xx", "xCollection")
    ->bindR("main_img", "cover", "xCollection")
    // returns result
    ->get();

    $data[] = $obj->result;
}

$json = json_encode($data, JSON_UNESCAPED_UNICODE );
file_put_contents("data.json", $json);

print_r($data);



/* Some other Concepts */

// replication - if replication enabled, it check if some of source array keys match with class properties and sets them (if name/key is exactly same)

/* you can get just simple array like that:
[data] => Array
    (
        [0] => test
        [1] => test2
        [2] => test3
    )

    for it just initilize object with string:
*/
$data = new maestroerror\dataRemastery("test, test2, test3");
print_r($data->get());

// if you want anouther separator before initilize:
maestroerror\dataRemastery::$defaultSeparator = ";";
$data = new maestroerror\dataRemastery("test;test2;test3");
print_r($data->get());

// init with array or JSON for multidimensional:
$data = new maestroerror\dataRemastery([
    "id" => 1,
    "name" => "testing data",
    "categories" => "testing, data, remastery, by, maestroerror",
]);
// if you just print rawData property you will get same array
print_r($data->rawData);