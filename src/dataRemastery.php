<?php 
namespace maestroerror;

class dataRemastery {

    public static $VERSION = "0.2.1";

    /* Unconfirmed */
    public $reciver;
    // If you use reciveBySource you need to specify source - first argument of reciver
    public $source;
    public $sender;
    /* End Unconfirmed */

    public array $rawData;
    public array $result;
    
    static string $defaultSeparator = ',';
    protected string $separator;
    protected string $separatorX;

    // replication - if replication enabled, it check if some of source array keys match with class properties and sets them (if name/key is exactly same)
    protected bool $replicate = true;
    
    protected array $binder;

    // resolver has it's collections, where goes resolved results
    protected array $resolver = [
        // auto Collection Name
        'last' => [],
        // temporary collection name
        'temp' => []
    ];
    

    // fields
    // public $id;

    //options
    protected string $acn = "last"; // autoCollectionName
    protected string $tcn = "temp"; // tempCollectionName

    protected array $unsets = []; //



    public function __construct($rawData) {
        $this->build($rawData);
        $this->defineUnsets();
        return $this;
    }

    public function bind(string $propertyName, string $dataFieldName) {
        $this->binder[$propertyName] = $this->rawData[$dataFieldName];
        $this->set($this->binder);
        return $this;
    }

    // binds last or specified resolved value
    // $dataFieldName key or index
    public function bindR(string $propertyName, string|int $dataFieldName, string|bool $collectionName = false, $defaultValue = null) {
        $collectionName = $this->checkCollection($collectionName);

        if(isset($this->resolver[$collectionName][$dataFieldName])) {
            $this->binder[$propertyName] = $this->resolver[$collectionName][$dataFieldName];
        } else {
            if($defaultValue) {
                $this->binder[$propertyName] = $defaultValue;
            }
        }
        
        $this->set($this->binder);
        return $this;
    }

    // $array - key => value pairs, where key is data name (property) and value is data itself
    public function bindSome($array) {
        $this->binder = $this->binder + $array; 
        $this->set($this->binder);
        return $this;
    }

    // sets the value
    public function setValue(string $propertyName, string|int $value) {
        $this->binder[$propertyName] = $value;
        $this->set($this->binder);
        return $this;
    }

    // explodes string by separator and puts data in resolver for use in binder 
    // $bind is array with field names, which will binded to data one by one
    public function resolveAndBind(string $fieldName, array $bind, string|bool $collectionName = false) {
        $string = $this->rawData[$fieldName];
        $this->setCollection($collectionName, explode($this->separator, $string));
        $resolved = $this->resolver[$collectionName];
        $bData = [];
        $i = 0;
        foreach ($bind as $fieldName) {
            $bData[$fieldName] = $resolved[$i];
            $i++;
        }
        $this->bindSome($bData);
        return $this;
    }

    public function bindResolved(string $fieldName, string|bool $collectionName = false) {
        $collectionName = $this->checkCollection($collectionName);
        $this->binder[$fieldName] = $this->resolver[$collectionName];
        $this->set($this->binder);
        return $this;
    }

    
    public function resolve(string $fieldName, string|bool $collectionName = false) {
        $collectionName = $this->checkCollection($collectionName);
        $string = $this->rawData[$fieldName];
        $this->setCollection($collectionName, explode($this->separator, $string));
        $resolved = $this->resolver[$collectionName];
        return $this;
    }

    // resolve from binded filed or existing collection
    public function resolveIn(string $fieldName, string|bool $savedCollectionName = false, $newCollectionName = false) {
        if($savedCollectionName) {
            $string = $this->resolver[$savedCollectionName][$fieldName];
        } else {
            $string = $this->{$fieldName};
        }
        $newCollectionName = $this->checkCollection($newCollectionName);
        $this->setCollection($newCollectionName, explode($this->separator, $string));
        return $this;
    }

    public function buildFromString($string, $fieldName) {
        $collectionName = $this->checkCollection(false);
        $this->setCollection($collectionName, explode($this::$defaultSeparator, $string));
        $resolved = $this->resolver[$collectionName];
        $rawData[$fieldName] = $resolved;
        $this->_data = "";
        return $rawData;
    }

    // in this case separator separates key->value pairs and separatorX separates key from value
    public function resolveX(string $fieldName, string|bool $collectionName = false) {
        $xfields = $this->rawData[$fieldName];
        $array = explode($this->separator, $xfields);
        $systemed = [];
        foreach ($array as $dataItem) {
            $item = explode($this->separatorX, $dataItem);
            $systemed[$item[0]] = $item[1];
        }
        $this->setCollection($collectionName, $systemed);
        return $this;
    }

    /** OPTIONS */

    public function noReplicate() {
        $this->replicate = false;
        return $this;
    }

    public function separator($separator) {
        $this->separator = $separator;
        return $this;
    }

    public function separatorX($separator) {
        $this->separatorX = $separator;
        return $this;
    }

    public function setReciver(Object $reciver) {
        $this->reciver = $reciver;
        return $this;
    }

    public function setSender(Object $sender) {
        $this->sender = $sender;
        return $this;
    }

    public function setSource($source) {
        $this->source = $source;
        return $this;
    }

    public function defineUnsets() {
        $this->unsets = [
            "reciver",
            "source",
            "sender",
            "rawData",
            "result",
            "separator",
            "separatorX",
            "binder",
            "replicate",
            "resolver",
            "acn",
            "tcn",
            "unsets",
            "*replicate",
            "*resolver",
            "*acn",
            "*tcn",
            "*unsets",
        ];
        return $this;
    }


    /** MANAGEMENT */
    
    // checks collectionName and sets default if not set
    protected function checkCollection($collectionName) {
        if (!$collectionName) {
            $collectionName = $this->acn;
        }
        return $collectionName;
    }

    // sets collection da temp collection
    protected function setCollection($collectionName, $value) {
        $collectionName = $this->checkCollection($collectionName);
        $this->resolver[$collectionName] = $value;
        $this->resolver[$this->tcn] = $value;
    }

    // prepare Result
    protected function prepare() {
        $data = $this;
        $unsets = $this->unsets;
        foreach ($unsets as $unset) {
            // echo $unset;
            unset($data->{$unset});
        }
        $data = (array) $data;
        if (!empty($data)) {
            $this->result = $data;
        }
    }

    // try - if string is Json return array, else return string back
    protected function tryJSON($string, $returnString = true) {
        $arr = json_decode($string, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $arr;
        } else {
            return ($returnString) ? $string : false;
        }
    }

    
    /** Main methods */

    // build - $field is used only when building from string, without key
    public function build($rawData, $field = "_data") {
        // check if object and convert into array
        if (is_object($rawData)) {
            $rawData = json_decode(json_encode($rawData), true);
        }
        // if string, try to json_decode it
        if (is_String($rawData)) {
            $rawData = $this->tryJSON($rawData);
        }
        // if string anyway, try to resolve it by separators
        if (is_String($rawData)) {
            $rawData = $this->buildFromString($rawData, $field);
        }

        // set if replication is enabled
        if ($this->replicate) {
            if (is_array($rawData)) {
                $this->set($rawData);
            }
        }
        $this->rawData = $rawData;
        return $this;
    }

    // constructs sender and returns sender object (send)
    public function send() {
        if(!empty($this->sender)) {
            return $this->sender($this->result);
        }
    }

    // constructs reciver and returns reciver object (recive)
    public function recive() {
        if(!empty($this->reciver)) {
            $this->build($this->reciver());
        }
    }
    // recive by source - construct reciver with source argument
    public function reciveBySource() {
        if(!empty($this->reciver) && !empty($this->source)) {
            $this->build($this->reciver($this->source));
        }
    }
    
    // Set properties by array
    protected function set(Array $fields) {
        foreach ($fields as $prop => $value) {
            //var_dump($fields);
            if(property_exists($this,$prop)) {
                $this->{$prop} = $value;
            }
        }
        return $this;
    }

    // Get result
    public function get() {
        $this->prepare();
        if ($this->result) {
            return $this->result;
        } else {
            throw new Exception('There is no result');
        }
    }


}