<?php

namespace TKMON\Action\Expose\System;

class Login extends \TKMON\Action\Base
{
    public function getActions() {
        return array('Index', 'Login', 'Logout');
    }

    public function actionIndex() {
        $output = new \TKMON\Mvc\Output\TwigTemplate($this->container['template']);
        $output->setTemplateName('forms/login.html');
        return $output;
    }

    public function actionLogin() {
        $params = $this->container['params'];
        $user = $this->container['user'];

        $r = new \TKMON\Mvc\Output\JsonResponse();

        try {
            $user->doAuthenticate($params->getParameter('username'), $params->getParameter('password'));
            $user->write();
            $r->setSuccess(true);
        } catch (\TKMON\Exception\UserException $e) {
            $r->setSuccess(false);
            $r->addException($e);
        }


        $r['authenticated'] = $user->getAuthenticated();
        return $r;
    }

    public function actionLogout() {
        $session = $this->container['session'];
        $session->destroySession();

        $template = new \TKMON\Mvc\Output\TwigTemplate($this->container['template']);
        $template->setTemplateName('forms/logout.html');
        return $template;
    }
}
