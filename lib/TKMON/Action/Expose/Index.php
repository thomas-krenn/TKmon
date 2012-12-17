<?php
namespace TKMON\Action\Expose;

class Index extends \TKMON\Action\Base
{
    public function getActions() {
        return array('Index');
    }

    public function actionIndex() {
        $user = $this->container['user'];

        $view = new \TKMON\Mvc\Output\TwigTemplate($this->container['template']);

        if ($user->getAuthenticated() === true) {
            $view->setTemplateName('views/welcome.html');
        } else {
            $view->setTemplateName('views/welcome-guest.html');
        }

        return $view;
    }
}
