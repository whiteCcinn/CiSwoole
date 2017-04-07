<?php
/**
 * @param array
 *   <li> keyowrd = '搜索关键字' </li>
 *   <li> post = 'post请求方法' </li>
 *   <li> get = 'get请求方法' </li>
 */
$route['api'] = [
  'keyword' => 'cmd',
  'post'    => [
    'index' => ['d' => '', 'c' => 'Hh', 'm' => 'index2'],
  ],
  'get'     => [

  ],
];