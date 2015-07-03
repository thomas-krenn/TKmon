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
 * @copyright 2012-2014 NETWAYS GmbH <info@netways.de>
 */

namespace TKMON\Action\Expose\System\Configuration;

use NETWAYS\Common\ArrayObject;
use NETWAYS\Http\CgiParams;
use NETWAYS\IO\Exception\ProcessException;
use TKMON\Action\Base;
use TKMON\Exception\ModelException;
use TKMON\Model\System\Configuration\Importer;
use TKMON\Model\System\Configuration\Exporter;
use TKMON\Model\System\Configuration\ZipFile;
use TKMON\Model\System;
use TKMON\Mvc\Output\JsonResponse;
use TKMON\Mvc\Output\TwigTemplate;

/**
 * Action to handle basic configuration tasks
 *
 * @package TKMON\Action
 * @author Marius Hein <marius.hein@netways.de>
 */
class Backup extends Base
{
    /**
     * Show the form
     * @param ArrayObject $params
     * @return TwigTemplate
     */
    public function actionIndex(ArrayObject $params)
    {
        $template = new TwigTemplate($this->container['template']);
        $template->setTemplateName('views/System/Configuration/Backup.twig');
        return $template;
    }

    /**
     * Download configuration dump
     * @param ArrayObject $params
     */
    public function actionDownloadConfiguration(ArrayObject $params)
    {

        $exporter = new Exporter($this->container);

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

    /**
     * Restore system configuration
     *
     * @param ArrayObject $params
     * @return JsonResponse
     * @throws \Exception|ProcessException
     * @throws ModelException
     */
    public function actionRestoreConfiguration(ArrayObject $params)
    {
        static $validContentTypes = array(
            'application/zip',
            'application/x-zip-compressed'
        );

        $response = new JsonResponse();

        try {
            $password = $params->get('password');
            $ignoreManifestErrors = $params->get('ignore_manifest');

            /** @var $params CgiParams */
            $params = $this->container['params'];
            $contentType = $params->getParameter('CONTENT_TYPE', null, 'header');

            if (in_array($contentType, $validContentTypes) === false) {
                throw new ModelException(
                    'Content type is: "'
                    . $contentType
                    . '". Should one of "'
                    . implode('", "', $validContentTypes)
                    . '".'
                );
            }

            $zipFile = new ZipFile($this->container);

            if ($password) {
                $zipFile->setPassword($password);
            }

            try {
                $directory = $zipFile->extractStandardInToDisk();
            } catch (ProcessException $e) {
                $msg = $e->getMessage();
                if (strpos($msg, 'password') !== false) {
                    throw new ModelException(_('Password is empty or does not match'));
                }

                throw $e;
            }

            $importer = new Importer($this->container);
            if ($ignoreManifestErrors === '1') {
                $importer->setSoftAssert(true);
            }
            $importer->fromDirectory($directory, (($password) ? true : false));

            $response->setSuccess(true);

        } catch (\Exception $e) {
            $response->addException($e);
        }

        return $response;
    }
}
