<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Handler extends BaseController
{

  public function index()
  {
    return $this->response('请求无效命令');
  }
}
