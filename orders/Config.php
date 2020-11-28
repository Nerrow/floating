<?php
namespace PHPAuth;

/**
 * Auth Config class
 */
class Config
{
    protected $dbh;
    protected $config;
    protected $config_table = 'config';

    /**
     *
     * Config::__construct()
     *
     * @param \PDO $dbh
     * @param string $config_table
     */
    public function __construct(\PDO $dbh, $config_table = 'config')
    {
        $this->dbh = $dbh;
		
		if (func_num_args() > 1) {
			$this->config_table = $config_table;
		}
		
        $query = $this->dbh->query("SELECT * FROM {$this->config_table}");
        $this->config = $query->fetchAll(\PDO::FETCH_KEY_PAIR);
    }

    /**
     * Config::__get()
     *
     * @param mixed $setting
     * @return string
     */
    public function __get($setting)
    {
        return $this->config[$setting];
    }

    /**
     * @return array
     */
    public function getAll()
    {
        return $this->config;
    }

    /**
     * Config::__set()
     *
     * @param mixed $setting
     * @param mixed $value
     * @return bool
     */
    public function __set($setting, $value)
    {
        $query = $this->dbh->prepare("UPDATE {$this->config_table} SET value = ? WHERE setting = ?");

        if ($query->execute(array($value, $setting))) {
            $this->config[$setting] = $value;

            return true;
        }

        return false;
    }

    /**
     * Config::override()
     *
     * @param mixed $setting
     * @param mixed $value
     * @return bool
     */
    public function override($setting, $value)
    {
        $this->config[$setting] = $value;

        return true;
    }
}

ini_set('display_errors', 'On');