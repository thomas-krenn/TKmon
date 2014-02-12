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

namespace TKMON\Model\System\Configuration;

/**
 * Manifest for configuration data
 * @package TKMON\Model
 * @author Marius Hein <marius.hein@netways.de>
 */
class Manifest extends Base
{
    /**
     * Manifest class version (data map attributes)
     */
    const MANIFEST_VERSION = '1.0.0';

    /**
     * Array of data attributes
     *
     * What can be exported and imported
     *
     * @var string[]
     */
    private static $dataMap = array(
        'manifest'      => 'manifestVersion',
        'version'       => 'softwareVersion',
        'created'       => 'created',
        'baseDir'       => 'baseDir',
        'subObjects'    => 'subObjects',
        'list'          => 'fileList',
        'password'      => 'password',
        'sha1'          => 'sha1Hash'
    );

    /**
     * Version of data attributes
     * @var string
     */
    private $manifestVersion = self::MANIFEST_VERSION;

    /**
     * Version of software
     * @var string
     */
    private $softwareVersion;

    /**
     * Archive password set
     * @var bool
     */
    private $password = false;

    /**
     * Where from the export was
     * @var string
     */
    private $baseDir;

    /**
     * Hash of whole directory content
     * @var string
     */
    private $sha1Hash;

    /**
     * Which modules are exported
     * @var string[]
     */
    private $subObjects = array();

    /**
     * Detailed file list
     * @var string[]
     */
    private $fileList = array();

    /**
     * Created timestamp
     * @var \DateTime
     */
    private $created;

    /**
     * Creates an pre configured iterator
     * @param $targetDir
     * @return \RecursiveIteratorIterator
     */
    private function createDirectoryIterator($targetDir)
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                $targetDir,
                \RecursiveDirectoryIterator::SKIP_DOTS
            ),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        return $iterator;
    }

    /**
     * Configure object from directory
     * @param string $targetDir Directory to load from
     */
    public function createFromDirectory($targetDir)
    {

        $this->baseDir = $targetDir;

        foreach (self::$exportParts as $part) {
            $dir = $targetDir. DIRECTORY_SEPARATOR. $part;
            if (file_exists($dir)) {
                $this->subObjects[] = $part;
            }
        }

        /** @var $fileInfo \SplFileInfo */
        $fileInfo = null;

        $iterator = $this->createDirectoryIterator($targetDir);

        foreach ($iterator as $fileInfo) {

            // Ignore manifest file
            if ($fileInfo->getFilename() === self::FILE_META_NAME) {
                continue;
            }

            $file = str_replace($targetDir, '', $fileInfo->getRealPath());
            $this->fileList[] = $file;
        }

        $this->setSha1Hash($this->generateSha1FromDirectory($targetDir));

        $this->setCreated(new \DateTime());
    }

    /**
     * Creates an sha1 hash from directory
     * @param string $directory
     * @return string
     */
    public function generateSha1FromDirectory($directory)
    {
        /** @var $fileInfo \SplFileInfo */
        $fileInfo = null;

        $iterator = $this->createDirectoryIterator($directory);
        $hashes = array();

        foreach ($iterator as $fileInfo) {

            // Ignore manifest file
            if ($fileInfo->getFilename() === self::FILE_META_NAME) {
                continue;
            }

            $hashes[] = sha1(file_get_contents($fileInfo->getRealPath()));
        }

        return sha1(implode('-', $hashes));
    }

    /**
     * Tests a directory against reference hash
     * @param string $hash
     * @param string $directory
     * @return bool
     */
    public function testHash($hash, $directory)
    {
        return ($hash === $this->generateSha1FromDirectory($directory))
            ? true : false;
    }

    /**
     * Write manifest to a manifest file
     * @param string $fileName
     */
    public function writeToFile($fileName)
    {
        file_put_contents($fileName, $this->toString());
    }

    /**
     * Setter for softwareVersion
     * @param string $softwareVersion
     */
    public function setSoftwareVersion($softwareVersion)
    {
        $this->softwareVersion = $softwareVersion;
    }

    /**
     * Getter for softwareVersion
     * @return string
     */
    public function getSoftwareVersion()
    {
        return $this->softwareVersion;
    }

    /**
     * Setter for baseDir
     * @param string $baseDir
     */
    public function setBaseDir($baseDir)
    {
        $this->baseDir = $baseDir;
    }

    /**
     * Getter for baseDir
     * @return string
     */
    public function getBaseDir()
    {
        return $this->baseDir;
    }

    /**
     * Setter for created
     * @param \DateTime $created
     */
    public function setCreated(\DateTime $created)
    {
        $this->created = $created;
    }

    /**
     * Getter for created
     * @return string
     */
    public function getCreated()
    {
        return $this->created->format(\DateTime::ISO8601);
    }

    /**
     * Setter for fileList
     * @param array $fileList
     */
    public function setFileList(array $fileList)
    {
        $this->fileList = $fileList;
    }

    /**
     * Getter for fileList
     * @return string[]
     */
    public function getFileList()
    {
        return $this->fileList;
    }

    /**
     * Setter for manifestVersion
     * @param string $manifestVersion
     */
    public function setManifestVersion($manifestVersion)
    {
        $this->manifestVersion = $manifestVersion;
    }

    /**
     * Getter for manifestVersion
     * @return string
     */
    public function getManifestVersion()
    {
        return $this->manifestVersion;
    }

    /**
     * Setter for password flag
     * @param boolean $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * Getter for password flag
     * @return bool
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Setter for sha1Hash
     * @param string $sha1Hash
     */
    public function setSha1Hash($sha1Hash)
    {
        $this->sha1Hash = $sha1Hash;
    }

    /**
     * Getter for sha1Hash
     * @return string
     */
    public function getSha1Hash()
    {
        return $this->sha1Hash;
    }

    /**
     * Setter for subObjects
     * @param string[] $subObjects
     */
    public function setSubObjects(array $subObjects)
    {
        $this->subObjects = $subObjects;
    }

    /**
     * Getter for subObjects
     * @return string[]
     */
    public function getSubObjects()
    {
        return $this->subObjects;
    }

    /**
     * Flag setter for password
     * @param bool $flag
     */
    public function hasPassword($flag = true)
    {
        $this->password = $flag;
    }

    /**
     * Converts this object to a stdClass
     * @return \stdClass
     */
    public function toDataVoyager()
    {
        $object = new \stdClass();
        foreach (self::$dataMap as $attribute => $map) {
            $getter = 'get'. ucfirst($map);
            $object->{$attribute} = $this->$getter();
        }
        return $object;
    }

    /**
     * Configures this object from stdClass
     * @param \stdClass $object
     */
    public function fromDataVoyager(\stdClass $object)
    {
        foreach ($object as $property => $value) {
            if (array_key_exists($property, self::$dataMap)) {
                $setter = 'set'. ucfirst(self::$dataMap[$property]);
                $this->$setter($value);
            }
        }
    }

    /**
     * Loads from a manifest file
     * @param string $fileName
     */
    public function fromJsonFile($fileName)
    {

        $voyager = json_decode(file_get_contents($fileName));

        $tstamp = $voyager->created;

        $object = new \DateTime($tstamp);

        $voyager->created = $object;

        $this->fromDataVoyager($voyager);
    }

    /**
     * Test if objects are the same
     * @param Manifest $toTest
     * @throws \TKMON\Exception\ModelException
     */
    public function assertEquality(Manifest $toTest)
    {
        $errors = array();
        foreach (self::$dataMap as $accessor => $propertyName) {

            if ($accessor === 'created' || $accessor === 'baseDir') {
                continue;
            }

            $getter = 'get'. ucfirst($propertyName);

            if ($this->$getter() !== $toTest->$getter()) {
                $errors[] = $accessor;
            }
        }

        if (count($errors)) {
            throw new \TKMON\Exception\ModelException(
                'Manifest errors in following properties: '. implode(', ', $errors)
            );
        }
    }

    /**
     * Converts object to string
     * @return string
     */
    public function toString()
    {
        return json_encode($this->toDataVoyager());
    }

    /**
     * PHP magic toString conversion
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }
}
