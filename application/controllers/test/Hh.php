<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Hh extends BaseController
{
  public function index2()
  {
    $model = new login_model();
    $ret = $model->get_user();
    var_dump($ret);
    return $this->response('111');
  }
}
