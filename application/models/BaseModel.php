<?php

/**
 * ---------------------------------------
 * #class_name
 * ---------------------------------------
 * [功能描述]
 * #class_info
 * ---------------------------------------
 * @author Caiwh <caiwh@adnonstop.com>
 * @date   2017/3/2
 * ---------------------------------------
 */
class BaseModel
{
  public $handler;

  private $dbPool;

  public function __construct()
  {
    if (!is_resource($this->handler))
    {
      $this->dbPool        = \Storage::$dbPool;
      if(count($this->dbPool)>0)
      {
        $this->handler = $this->dbPool->pop();
      }
    }
  }

  public function __destruct()
  {
    if(is_resource($this->handler))
    {
      $this->dbPool->push($this->handler);
      $this->handler = null;
    }
  }
}