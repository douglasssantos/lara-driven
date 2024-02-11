<?php

namespace Larakeeps\LaraDriven\Traits;

trait ApiResponses
{
    private bool $viewResponse = true;
    public function createResponse($message = null, $data = null, $errors = null, int $statusCode = null, $status = null, bool $breakCode = false)
    {
        if(!$this->viewResponse){

            if(!empty($data)) {
                return $data;
            }

            if(!empty($errors)) {
                return $errors;
            }

            if(!empty($message)) {
                return $message;
            }

            return false;
        }

        $response = [];

        $response["message"] = $message;

        if(is_string($data))$data = [$data];
        $response["data"] = $data;

        if(is_string($errors))$errors = [$errors];
        $response["errors"] = $errors;

        $response["statusCode"] = $statusCode;

        if(!is_null($status))$response["status"] = $status;

        if($breakCode){
            header("Content-Type: application/json");
            http_response_code($statusCode);
            echo json_encode($response);
            exit;
        }

        return response()->json($response, $statusCode);
    }
    public function createResponseCustom($message = null, int $statusCode = null, bool $breakCode = false, ...$args)
    {

        $response = [];

        if(!is_null($message))$response["message"] = $message;

        if(!is_null($statusCode))$response["statusCode"] = $statusCode;

        foreach($args as $arg){
            $response[key($arg)] = $arg[key($arg)];
        }

        if(!$this->viewResponse){

            if(!empty($response)) {
                return $response;
            }

            return false;
        }

        if($breakCode){
            header("Content-Type: application/json");
            http_response_code($statusCode);
            echo json_encode($response);
            exit;
        }

        return response()->json($response, $statusCode);
    }
    public function success($message, $data = [], $statusCode = 200, $breakCode = true)
    {
        return $this->createResponse($message, $data, null, $statusCode, true, $breakCode);
    }
    public function successWithoutContent($statusCode = 204, $breakCode = true)
    {
        return $this->createResponse("", [], null, $statusCode, true, $breakCode);
    }
    public function notFound($messageError, $data = [], $breakCode = true)
    {
        return $this->createResponse("Nenhuma informação existente!", $data, $messageError, 404, false, $breakCode);
    }
    public function unauthorized($messageError, $data = [])
    {
        return $this->createResponse($messageError, $data, $messageError,401, false, true);
    }
    public function forbidden($messageError, $data = [])
    {
        return $this->createResponse($messageError, $data, $messageError,403, false, true);
    }
    public function fail($messageError, $data = [], $statusCode = 400, $breakCode = true)
    {
        return $this->createResponse($messageError, $data, $messageError, $statusCode, false, $breakCode);
    }
    public function locked($messageError, $data = [], $statusCode = 423, $breakCode = true)
    {
        return $this->createResponse($messageError, $data, $messageError, $statusCode, false, $breakCode);
    }
    public function incomplete($messageError, $data = [], $statusCode = 400, $breakCode = true)
    {
        return $this->createResponse($messageError, $data, $messageError, $statusCode, false, $breakCode);
    }
    public function pending($messageError, $data = [], $statusCode = 400, $breakCode = true)
    {
        return $this->createResponse($messageError, $data, $messageError, $statusCode, false, $breakCode);
    }
    public function timeout($messageError, $errors = [], int $statusCode = 408)
    {
        return $this->createResponse($messageError, [], $errors, $statusCode, false, true);
    }
    public function failRequest($messageError, $errors = [], int $statusCode = 400)
    {
        return $this->createResponse($messageError, [], $errors, $statusCode, false, true);
    }
    public function internalError($messageError, $data = [], int $statusCode = 500)
    {
        return $this->createResponse($messageError, $data, $messageError, $statusCode, false, true);
    }

}
