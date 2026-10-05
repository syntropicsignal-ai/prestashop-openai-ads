<?php

class OpenaiadsfeedCronModuleFrontController extends ModuleFrontController
{
    public $ssl = true;

    public function initContent()
    {
        $this->ajax = true;
        parent::initContent();

        header('Content-Type: application/json; charset=utf-8');
        $token = Tools::getValue('token');
        if (!$this->module->cronUrlIsAuthorized($token)) {
            http_response_code(403);
            die(json_encode(['status' => 'forbidden']));
        }

        try {
            $result = $this->module->syncCatalog();
            die(json_encode(['status' => 'ok', 'result' => $result]));
        } catch (Exception $exception) {
            http_response_code(502);
            die(json_encode(['status' => 'error', 'message' => $exception->getMessage()]));
        }
    }
}
