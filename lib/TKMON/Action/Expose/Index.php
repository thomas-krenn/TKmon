<?php
namespace TKMON\Action\Expose;

class Index extends \TKMON\Action\Base
{
    public function getActions() {
        return array('Index');
    }

    public function actionIndex() {
        return "OK";
    }
}
