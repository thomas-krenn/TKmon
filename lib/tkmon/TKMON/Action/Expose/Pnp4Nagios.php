<?php
/**
 * Created by PhpStorm.
 * User: mhein
 * Date: 19/06/15
 * Time: 15:41
 */

namespace TKMON\Action\Expose;


use NETWAYS\Common\ArrayObject;
use TKMON\Action\Base;
use TKMON\Model\Icinga\Pnp4Nagios as PnpModel;

class Pnp4Nagios extends Base
{
    public function actionImage(ArrayObject $params)
    {
        $config = $this->container['config'];
        $pnpModel = new PnpModel($this->container);
        $pnpModel->setAccessUrl($config->get('pnp4nagios.url'));
        $pnpModel->setPerfdataPath($config->get('pnp4nagios.perfdata'));
        $cls = new \stdClass();
        $cls->host_name = $params['host'];
        if (isset($params['srv'])) {
            $cls->service_description = $params['srv'];
        }
        $pnpModel->setObject($cls);
        $imageUrl = $pnpModel->getUrl(true, (array)$params);
        $proxy = new \NETWAYS\Http\SimpleProxy();
        $proxy->setBaseUrl($imageUrl);
        $proxy->setHttpAuth(
            $config->get('icinga.tkuser'),
            $config->get('icinga.tkpasswd')
        );
        $content = $proxy->getContent();
        $info = $proxy->getInfo();
        // if ($info['http_code'] === 200) {
            header('Content-type: ' . $info['content_type']);
            header('Content-length: ' . strlen($content));
            echo $content;
            exit(0);
        //}
    }
}