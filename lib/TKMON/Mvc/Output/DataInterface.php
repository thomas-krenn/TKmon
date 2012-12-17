<?php

namespace TKMON\Mvc\Output;

interface DataInterface
{
    public function toString();
    public function getData();
    public function __toString();
}
