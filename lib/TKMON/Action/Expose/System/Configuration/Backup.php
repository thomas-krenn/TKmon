<?php
/**
 * This file is part of TKMON
 *
 * TKMON is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * TKMON is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with TKMON.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Marius Hein <marius.hein@netways.de>
 * @copyright 2012-2013 NETWAYS GmbH <info@netways.de>
 */

namespace TKMON\Action\Expose\System\Configuration;

/**
 * Action to handle basic configuration tasks
 *
 * @package TKMON\Action
 * @author Marius Hein <marius.hein@netways.de>
 */
class Backup extends \TKMON\Action\Base
{
    /**
     * Show the form
     * @param \NETWAYS\Common\ArrayObject $params
     * @return \TKMON\Mvc\Output\TwigTemplate
     */
    public function actionIndex(\NETWAYS\Common\ArrayObject $params)
    {
        $template = new \TKMON\Mvc\Output\TwigTemplate($this->container['template']);
        $template->setTemplateName('views/System/Configuration/Backup.twig');
        return $template;
    }

    /**
     * Action to handle reboot
     * @param \NETWAYS\Common\ArrayObject $params
     * @return \TKMON\Mvc\Output\JsonResponse
     * @throws \TKMON\Exception\ModelException
     */
    public function actionApplianceReboot(\NETWAYS\Common\ArrayObject $params)
    {
        $response = new \TKMON\Mvc\Output\JsonResponse();

        $systemModel = new \TKMON\Model\System($this->container);

        try {
            if ($params->get('reboot', false) === '1') {
                $systemModel->doReboot();
                $response->setSuccess(true);
            } else {
                throw new \TKMON\Exception\ModelException("Not properly parametrized: Action ApplianceReboot");
            }
        } catch (\Exception $e) {
            $response->addException($e);
        }

        return $response;
    }

    /**
     * Download configuration dump
     * @param \NETWAYS\Common\ArrayObject $params
     */
    public function actionDownloadConfiguration(\NETWAYS\Common\ArrayObject $params)
    {

        $exporter = new \TKMON\Model\System\Configuration\Exporter($this->container);

        try {
            list($seconds, $micros) = explode('.', microtime(true));
            $fileName = $this->container['tmp_dir'].
                DIRECTORY_SEPARATOR.
                strftime('%Y%m%d').
                '-'. $seconds.
                '-'. $micros.
                '-'. posix_getpid().
                '-dump.zip';

            $exporter->setFile($fileName);

            if ($params->get('password')) {
                $exporter->setPassword($params->get('password'));
            }

            $exporter->toFile();

            header('Content-disposition: attachment; filename='. basename($fileName));
            header('Content-type: application/octet-stream');
            readfile($fileName);

        } catch (\Exception $e) {
            printf('<h4>Error</h4><code>%s</code>', nl2br($e->getMessage()));
        }

        $exporter->cleanUp();

        exit(0);
    }

    public function actionRestoreConfiguration(\NETWAYS\Common\ArrayObject $params)
    {

    }
}
