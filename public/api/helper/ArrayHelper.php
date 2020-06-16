<?php
/*
 * Functions to manipulate arrays like JavaScript
 * Author: Elias Lazcano Castro Neto
 */

class ArrayHelper
{
  public $array = [];

  /**
   * ArrayHelper constructor.
   * @param array $array
   */
  public function __construct(array $array)
  {
    $this->array = $array;
  }
  public function __call($name, $arguments)
  {
    return self::$name($this->array, $arguments[0]);
  }

  public static function filter($array, $function)
  {
    return array_filter($array, $function);
  }
  public static function map($array, $function)
  {
    return array_map($function, $array);
  }
  public static function find($array, $function)
  {
    $filtered = self::filter($array, $function);
    $filtered = array_values($filtered);
    if (count($filtered)) return $filtered[0];
    else return null;
  }
  public static function group_by($key, $data) {
    $result = array();
    foreach($data as $val) {
      if(array_key_exists($key, $val)){
        $result[$val[$key]][] = $val;
      }else{
        $result[""][] = $val;
      }
    }

    return $result;
  }
}
