<?php

/**
 * ---------------------------------------
 * #class_name
 * ---------------------------------------
 * [功能描述]
 * #class_info
 * ---------------------------------------
 * @author Caiwh
 * @date   2017/3/2
 * ---------------------------------------
 */
class login_model extends BaseModel
{
  public function __construct()
  {
    parent::__construct();
  }

  public function get_user($param = [])
  {
    $sql = 'select * from fg.user where user_id = 10000';
    $ret = $this->handler->query($sql);

    return $ret;
  }
}