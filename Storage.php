<?php

/**
 * Created by PhpStorm.
 * User: Cwh-Macbook
 * Date: 2017/2/26
 * Time: 16:55
 */
class Storage
{
  /**
   * ???????
   * @var
   */
  static public $head;

  /**
   * ????????
   * @var
   */
  static public $request;

  /**
   * ???????
   * @var
   */
  static public $response;

  /**
   * get????????????
   * @var
   */
  static public $get;

  /**
   * post????????????
   * @var
   */
  static public $post;

  /**
   * ????????
   * @var
   */
  static public $request_params;

  /**
   * cookies????????
   * @var
   */
  static public $cookies;

  /**
   * sessinn????????
   * @var
   */
  static public $session;

  /**
   * ???????
   * @var
   */
  static public $request_time;


  static public $http;

  static public $controller;

  static public $method;

  static public $module;

  static public $cmd;

  static public $dbPool;

  /**
   * ??й??????
   * @var
   */
  static public $exe_time;

  private function __construct()
  {
  }

  private function __clone()
  {
  }

  function __destruct()
  {
    $vars = get_class_vars(__CLASS__);
    foreach ($vars as $property => &$value)
    {
      unset($value);
    }
  }
}