<?php

namespace TKMON\Action\Expose\System;

class Login extends \TKMON\Action\Base
{
    public function getActions() {
        return array('Index', 'Login');
    }

    public function actionIndex() {
        $template = $this->createTemplate('forms/login.html');
        return $template->render(array());
    }

    public function actionLogin() {
        return json_encode($this->container['params']->getAll('request'));
    }
}
