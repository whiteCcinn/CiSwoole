<?php
defined('BASEPATH') OR exit('No direct script access allowed');

abstract class BaseController extends CI_Controller
{

  /**
   * @param $result
   * @param int $type
   *   <li>  type = 'empty' , 无结构体 </li>
   *   <li>  type = 'info' , 单结构体 </li>
   *   <li>  type = 'list' , 多结构体 </li>
   * @return int
   */
  public function response($data, $type = 'empty')
  {
    $result = ['cmd' => Storage::$cmd, 'code' => -6, 'info' => '空消息'];

    if (!empty($data))
    {
      switch ($type)
      {
        case 'info':
        case 'list':
        case 'empty':
          $result['code'] = 0;
          $result['info'] = '操作成功';
          if ($type != 'empty')
          {
            $result[$type] = $data;
          }
          else
          {
            // 自定义返回无结构体消息信息
            $result['code'] = -99;
            $result['info'] = $data;
          }
          break;
        default :
          $result['code'] = -7;
          $result['info'] = '返回错误结构体';
      }
    }

    echo json_encode($result, JSON_UNESCAPED_UNICODE);

    return 1;
  }
}
