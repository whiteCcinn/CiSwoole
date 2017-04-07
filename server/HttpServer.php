<?php

namespace Server;

use FiredGoods\HttpIndex;

class HttpServer
{
  public        $http;
  public static $instance;
  public static $level = 5;    //压缩等级，范围是1-9，等级越高压缩后的尺寸越小，但CPU消耗更多。默认为1
  public static $dbPool;       //MySql连接池
  const PoolSize = 10;

  /**
   * 初始化
   */
  private function __construct()
  {
    register_shutdown_function([$this, 'handleFatal']);

    $http = $this->http = new \swoole_http_server("0.0.0.0", 9501);

    $http->set(
      [
        'worker_num'    => 10,        //worker进程数量
        'daemonize'     => false,    //守护进程设置成true
        'max_request'   => 10000,    //最大请求次数，当请求大于它时，将会自动重启该worker
        'dispatch_mode' => 1,
        'log_file'      => '/wwwroot/share/fgserver/log/server.log',
      ]
    );

    $http->on('WorkerStart', [$this, 'onWorkerStart']);
    $http->on('ManagerStart', [$this, 'onManagerStart']);
    $http->on('request', [$this, 'onRequest']);
    $http->on('start', [$this, 'onStart']);
    $http->on('close', [$this, 'onClose']);
    $http->on('WorkerError', [$this, 'onWorkerError']);
    $http->on('WorkerError', [$this, 'onWorkerError']);
    $http->on('WorkerStop', [$this, 'onWorkerStop']);
    $http->start();
  }

  /**
   * @param \Swoole\Server $serv
   * @return bool
   */
  public function onStart(\Swoole\Server $serv)
  {
    $time = gmdate("Y-m-d H:i:s", time() + 8 * 60 * 60);
    $msg  = "->[onStart] PHP=" . PHP_VERSION . " swoole=" . SWOOLE_VERSION . " Master-Pid={$this->http->master_pid} Manager-Pid={$this->http->manager_pid}" . ' time=' . $time . PHP_EOL;
    $this->_write_file(dirname(__DIR__) . '/log/StdOutServer.log', $msg);
    swoole_set_process_name("fgApp:master");

    return true;
  }

  public function onClose(\Swoole\Server $serv, int $work_id)
  {
    // 回收对应进程申请的资源
    $time = gmdate("Y-m-d H:i:s", time() + 8 * 60 * 60);
    $msg  = "->[onClose] time=" . $time . PHP_EOL;
    $this->_write_file(dirname(__DIR__) . '/log/StdOutServer.log', $msg);
  }

  /**
   * ManagerProcessOnStart
   *
   * @param \Swoole\Server $serv
   */
  public function onManagerStart(\Swoole\Server $serv)
  {
    $time = gmdate("Y-m-d H:i:s", time() + 8 * 60 * 60);
    $msg  = "->[onManagerStart] time=" . $time . PHP_EOL;
    $this->_write_file(dirname(__DIR__) . '/log/StdOutServer.log', $msg);
    swoole_set_process_name("fgApp:manager");
  }

  /**
   * manager进程结束的时候调用
   *
   * @param \Swoole\Server $serv
   *
   * @return bool
   */
  public function onManagerStop(\Swoole\Server $serv)
  {
    $time = gmdate("Y-m-d H:i:s", time() + 8 * 60 * 60);
    $msg  = "->[onManagerStop] time=" . $time . PHP_EOL;
    $this->_write_file(dirname(__DIR__) . '/log/StdOutServer.log', $msg);

    return true;
  }

  /**
   * worker start时调用
   *
   * @param unknown $serv
   * @param int $worker_id
   */
  public function onWorkerStart(\Swoole\Server $serv, int $worker_id)
  {

    date_default_timezone_set('PRC');

    if ($serv->taskworker)
    {
      swoole_set_process_name("fgApp[{$worker_id}] : task" . 'er');
    }
    else
    {
      swoole_set_process_name("fgApp[{$worker_id}]: worker");
    }

    defined('APPLICATION_PATH') or define('APPLICATION_PATH', dirname(__DIR__));

    include APPLICATION_PATH . '/HttpIndex.php';

    include APPLICATION_PATH . '/Storage.php';

    \Storage::$http = $this->http;

    \Storage::$dbPool   = new \SplQueue();

    define('WORKER_ID',$worker_id);

  }

  /**
   * worker/tasker进程结束的时候调用
   * 在此函数中可以回收worker进程申请的各类资源
   *
   * @param \Swoole\Server $serv
   * @param int $work_id
   *
   * @return bool
   */
  public function onWorkerStop(\Swoole\Server $serv, int $work_id)
  {
    $time = gmdate("Y-m-d H:i:s", time() + 8 * 60 * 60);
    $msg  = "->[onWorkerStop] work_id" . $work_id . ' time=' . $time . PHP_EOL;
    $this->_write_file(dirname(__DIR__) . '/log/StdOutServer.log', $msg);

    return true;
  }

  public function onWorkerError(\Swoole\Server $serv, int $work_id, int $work_pid, int $exit_code)
  {
    $time = gmdate("Y-m-d H:i:s", time() + 8 * 60 * 60);
    $msg  = "->[onWorkerError] work_id=" . $work_id . ",work_pid=" . $work_pid . ",exit_code=" . $exit_code . ' time=' . $time . PHP_EOL;
    $this->_write_file(dirname(__DIR__) . '/log/StdOutServer.log', $msg);

    return true;
  }

  /**
   * 当request时调用
   *
   * @param unknown $request
   * @param unknown $response
   */
  public function onRequest($request, $response)
  {
    \Storage::$request_time = microtime(true);

    $response->header("Content-Type", "application/json;charset=utf-8");

    echo WORKER_ID,' : ',count(\Storage::$dbPool),PHP_EOL;

    // init mysql pool
    if (count(self::$dbPool) == 0 && count(self::$dbPool) < self::PoolSize)
    {
      for ($i = 0; $i < self::PoolSize; $i++)
      {
        $db  = new \Swoole\Coroutine\MySQL();
        $ret = $db->connect([
          'host'     => 'ip',
          'port'     => 3306,
          'user'     => '1111',
          'password' => '1111',
          'database' => 'fg',
        ]);

        // 创建成功,加入连接池
        if ($ret)
        {
          \Storage::$dbPool->push($db);
        }
      }
    }

    try
    {

      ob_start();

      Httpindex::getInstance($request, $response);

      $result = ob_get_contents();

      ob_end_clean();

      $result = empty($result) ? 'No message' : $result;

      if (($this->http->connection_info($response->fd) !== false))
      {
        /**
         * 如果没有response成功的话 , 通知监听服务器reload框架主进程
         */
        if (!$response->end($result))
        {
          $this->http->reload();
        }
      }

      unset($result);

    } catch (\Exception $e)
    {
      $response->end($e->getMessage());
    } finally
    {
      \Storage::$exe_time = microtime(true) - \Storage::$request_time;
    }
  }

  /**
   * 致命错误处理
   */
  public function handleFatal()
  {
    $error = error_get_last();
    if (isset($error['type']))
    {
      switch ($error['type'])
      {
        case E_ERROR :
          $severity = 'ERROR:Fatal run-time errors. Errors that can not be recovered from. Execution of the script is halted';
          break;
        case E_PARSE :
          $severity = 'PARSE:Compile-time parse errors. Parse errors should only be generated by the parser';
          break;
        case E_DEPRECATED:
          $severity = 'DEPRECATED:Run-time notices. Enable this to receive warnings about code that will not work in future versions';
          break;
        case E_CORE_ERROR :
          $severity = 'CORE_ERROR :Fatal errors at PHP startup. This is like an E_ERROR in the PHP core';
          break;
        case E_COMPILE_ERROR :
          $severity = 'COMPILE ERROR:Fatal compile-time errors. This is like an E_ERROR generated by the Zend Scripting Engine';
          break;
        default:
          $severity = 'OTHER ERROR';
          break;
      }
      $message = $error['message'];
      $file    = $error['file'];
      $line    = $error['line'];
      $log     = "$message ($file:$line)\nStack trace:\n";
      $trace   = debug_backtrace();
      foreach ($trace as $i => $t)
      {
        if (!isset($t['file']))
        {
          $t['file'] = 'unknown';
        }
        if (!isset($t['line']))
        {
          $t['line'] = 0;
        }
        if (!isset($t['function']))
        {
          $t['function'] = 'unknown';
        }
        $log .= "#$i {$t['file']}({$t['line']}): ";
        if (isset($t['object']) && is_object($t['object']))
        {
          $log .= get_class($t['object']) . '->';
        }
        $log .= "{$t['function']}()\n";
      }
      if (isset($_SERVER['REQUEST_URI']))
      {
        $log .= '[QUERY] ' . $_SERVER['REQUEST_URI'];
      }
      ob_start();
      include 'error_php.php';
      $log = ob_get_contents();
      ob_end_clean();
      \Storage::$response->end($log);
    }
  }

  public static function getInstance()
  {
    if (!self::$instance)
    {
      self::$instance = new self();
    }

    return self::$instance;
  }

  /**
   * 由于异步IO只能在worker进程使用，并且异步文件IO目前只是实现性质，所以还是采用了原生的PHP写法
   *
   * @param string $filename 文件名
   * @param string $msg 消息
   * @param int $flag 操作类型
   *
   * @return int
   */
  private function _write_file($filename, $msg, $flag = FILE_APPEND)
  {
    $ret = file_put_contents($filename, $msg, $flag);

    return $ret;
  }
}

HttpServer::getInstance();