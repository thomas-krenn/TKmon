<?php

namespace TKMON\Model;

/**
 * This is the current user
 */
class User
{

    /**
     * Session id for authenticated flag
     */
    const NS_AUTHENTICATED='user.authenticated';

    /**
     * Session id for id flag
     */
    const NS_USERID='user.id';

    const FIELD_ID='id';
    const FIELD_NAME='name';
    const FIELD_PASSWORD='password';
    const FIELD_SALT='salt';

    /**
     * @var \Pimple
     */
    protected $container;
    /**
     * @var bool
     */
    protected $authenticated=false;
    /**
     * @var
     */
    protected $name;
    /**
     * @var
     */
    protected $id;

    /**
     * @param \Pimple $container
     */
    public function __construct(\Pimple $container) {
        $this->container = $container;
    }

    /**
     * @param bool $authenticated
     */
    public function setAuthenticated($authenticated)
    {
        $this->authenticated = $authenticated;
    }

    /**
     * @return bool
     */
    public function getAuthenticated()
    {
        return $this->authenticated;
    }

    /**
     * @param \Pimple $container
     */
    public function setContainer($container)
    {
        $this->container = $container;
    }

    /**
     * @return \Pimple
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    public function initialize() {
        $session = $this->container['session'];
        if ($session->offsetExists(self::NS_AUTHENTICATED)) {
            $this->setAuthenticated((bool)$session->offsetGet(self::NS_AUTHENTICATED));
        }

        if ($session->offsetExists(self::NS_USERID) && $session[self::NS_USERID] > 0) {
            $data = $this->getUserData($session[self::NS_USERID]);
            $this->applyDataToObject($data);
        }

        if (!$this->id) {
            $this->setId(0);
            $this->setAuthenticated(false);
            $this->setName('Guest');
        }
    }

    public function write() {
        $session = $this->container['session'];
        $session[self::NS_USERID] = $this->getId();
        $session[self::NS_AUTHENTICATED] = $this->getAuthenticated();
    }

    private function getUserData($fieldValue, $fieldName=self::FIELD_ID) {
        $db = $this->container['db'];
        $statement = $db->prepare('SELECT * from user where '
            . $fieldName. '=:value LIMIT 1;');

        $statement->bindValue(':value', $fieldValue, \PDO::PARAM_STR);
        $re = $statement->execute();

        if ($re) {
            return $statement->fetch();
        }

        return false;
    }

    private function applyDataToObject(array $data) {
        $this->setId($data[self::FIELD_ID]);
        $this->setName($data[self::FIELD_NAME]);
    }

    public function doAuthenticate($username, $password) {

        if (!$username) {
            throw new \TKMON\Exception\UserException('Username is mandatory.');
        }

        if (!$password) {
            throw new \TKMON\Exception\UserException('Password is mandatory.');
        }

        $data = $this->getUserData($username, self::FIELD_NAME);
        if (is_array($data) === true) {
            $check_password = hash_hmac('md5', $password, $data[self::FIELD_SALT]);

            if ($check_password === $data[self::FIELD_PASSWORD]) {
                $this->setAuthenticated(true);
                $this->applyDataToObject($data);
                $this->write();
                return;
            }
        }

        throw new \TKMON\Exception\UserException('Could not authenticate user: '. $username);
    }
}
