<?php

namespace TKMON\Mvc\Output;

class JsonResponse extends Json
{

    const REF_TYPE_SERVER='server';
    const REF_TYPE_CLIENT='client';
    const REF_EXCEPTION='exception';
    const REF_UNKNOWN='unknown';

    public function __construct() {
        parent::__construct(array(
            'success'   => false,
            'errors'    => array(),
            'data'      => array()
        ));
    }

    public function setSuccess($success=true) {
        $this['success'] = $success;
    }

    public function addError($message, $refType=self::REF_TYPE_SERVER, $ref=self::REF_UNKNOWN) {
        $this['errors'][] = array(
            'message'   => $message,
            'reftype'   => $refType,
            'ref'       => $ref
        );
    }

    public function addException(\Exception $e) {
        $this->addError($e->getMessage(), self::REF_TYPE_SERVER, self::REF_EXCEPTION);
    }

    public function addData(array $data) {
        $this['data'][] = $data;
    }
}
