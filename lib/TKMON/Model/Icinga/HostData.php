<?php
/**
 * Created by JetBrains PhpStorm.
 * User: mhein
 * Date: 31/01/13
 * Time: 10:39
 * To change this template use File | Settings | File Templates.
 */
class HostData extends \ICINGA\Loader\FileSystem implements \TKMON\Interfaces\ApplicationModelInterface
{
    private $container;

    private $strategy;

    public function __construct(\Pimple $container)
    {
        $this->setContainer($container);

        $this->strategy = new \ICINGA\Loader\Strategy\HostServiceObjects();
        $this->setStrategy($this->strategy);
        $this->setPath($this->container['config']['icinga.dir.host']);
    }

    /**
     * Setter for DI container
     * @param \Pimple $container
     */
    public function setContainer(\Pimple $container)
    {
        $this->container = $container;
    }

    /**
     * Getter for DI configuration
     * @return \Pimple
     */
    public function getContainer()
    {
        return $this->container;
    }
}
