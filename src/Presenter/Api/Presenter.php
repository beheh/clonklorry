<?php

namespace Lorry\Presenter\Api;

use Lorry\Presenter\AbstractPresenter as LorryPresenter;
use Lorry\Exception\LorryException;

class Presenter extends LorryPresenter
{

    public function handle($method, $parameters)
    {
        try {
            return call_user_func_array(array($this, $method), $parameters);
        } catch (LorryException $ex) {
            $httpcode = $ex->getHttpCode();
            if (!$httpcode) {
                $httpcode = 500;
            }
            $httpmessage = $ex->getHttpMessage();
            if (!$httpmessage) {
                $httpmessage = 'Internal Server Error';
            }
            header('HTTP/1.1 '.$httpcode.' '.$httpmessage);
            $error = array('error' => $ex->getApiType(), 'message' => $ex->getMessage());
            $this->display($error);
        } catch (\Exception $ex) {
            $message = get_class($ex).': '.$ex->getMessage().' in '.$ex->getTraceAsString();
            $this->logger->error($message);
            header('HTTP/1.1 500 Internal Server Error');
            $error = array('error' => 'internal', 'message' => 'internal error');
            $this->display($error);
        }
        return false;
    }

    public function display($result)
    {
        header('Content-Type: text/json');
        echo json_encode($result);
    }
}
