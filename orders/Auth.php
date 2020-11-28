<?php

namespace PHPAuth;

/**
 * Auth class
 * Required PHP 5.4 and above.
 */

class Auth
{
    const HASH_LENGTH = 40;
    const COUPON_DISCOUNT = 10;
	
	public $config;
    protected $dbh;
	protected $islogged = NULL;
	protected $currentuser = NULL;
	
	
    /**
     * Initiates database connection
     */
    public function __construct(\PDO $dbh, $config)
    {
        $this->dbh = $dbh;
        $this->config = $config;
    }

    /**
     * Logs a user in
     * @param string $login
     * @param string $password
     * @param int $remember
     * @return array $return
     */
    public function login($login, $password, $remember = 0)
    {
        $return['error'] = false;
		
        $uid = $this->getUID($login);

        if (!$uid) {
            $return['login'] = "Неверный логин";
			$return['error'] = true;
			return $return;
        }

        $user = $this->getBaseUser($uid);

        if (!password_verify($password, $user['password'])) {
            $return['password'] = "Неверный пароль";
			$return['error'] = true;
			return $return;
        }
		
		if ($user['active'] != 1) {
			$return['message'] = "Учётная запись не активирована";
			$return['error'] = true;
			return $return;
		}
		
		$sessiondata = $this->addSession($user['uid'], $remember);

		if ($sessiondata == false) {
			$return['message'] = "Произошла системная ошибка (проблема с cookies, сессией или базой данных). Попробуйте ещё раз";
			$return['error'] = true;
			return $return;
		}
		
		$return['hash'] = $sessiondata['hash'];
		$return['expire'] = $sessiondata['expire'];
		
		$return['cookie_name'] = $this->config->cookie_name;
		
        return $return;
    }
	
    /**
    * Logs out the session, identified by hash
    * @param string $hash
    * @return boolean
    */

    public function logout($hash)
    {
        if (strlen($hash) != self::HASH_LENGTH) {
            return false;
        }

        return $this->deleteSession($hash);
    }
	
	/**
    * @return array $return
	*/
    public function register($email, $login, $password)
    {
        $return['error'] = false;
        
		$query = $this->dbh->prepare("SELECT email, login FROM {$this->config->table_users} WHERE email = ? OR login = ?");
        $query->execute(array($email, $login));
        $user = $query->fetch(\PDO::FETCH_ASSOC);
		
		if($email == '') {
			$return['email'] = 'Обязательное поле';
			$return['error'] = true;
		}
		elseif(!$this->validateEmail($email)) {
			$return['email'] = 'Неверный формат';
			$return['error'] = true;
		}
		elseif(isset($user['email']) && $user['email'] == $email) {
			$return['email'] = 'Такой еmail уже зарегистрирован в системе';
			$return['error'] = true;
		}
		
		if($login == '') {
			$return['login'] = 'Обязательное поле';
			$return['error'] = true;
		}
		elseif(!$this->validateLogin($login)) {
			$return['login'] = 'Допустимы латинские буквы и цифры. От 3 до 30 символов';
			$return['error'] = true;
		}
		elseif(isset($user['login']) && $user['login'] == $login) {
			$return['login'] = 'Такой логин уже зарегистрирован в системе';
			$return['error'] = true;
		}
		
        if(!$return['error']) {
			
			$password_hash = $this->getHash($password);
			
			$query = $this->dbh->prepare("INSERT INTO {$this->config->table_users} (email, login, password) VALUES (?, ?, ?)");

			if(!$query->execute(array($email, $login, $password_hash))) {
				$return['error'] = true;
			}
		}
		
        return $return;
    }
	
	/**
    * Send mail
    */

    public function sendMail($email, $subject, $message)
    {
		$headers = "From: Orders <noreply@lpcopier.ru>" . PHP_EOL;
		$headers .= "MIME-Version: 1.0" . PHP_EOL;
		$headers .= "Content-type: text/html; charset=utf-8" . PHP_EOL;
		
		return mail($email, $subject, $message, $headers);
    }
	
    /**
    * Hashes provided password with Bcrypt
    * @param string $password
    * @param string $password
    * @return string
    */

    public function getHash($password)
    {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => $this->config->bcrypt_cost]);
    }

    /**
    * Gets UID for a given login and returns an array
    * @param string $login
    * @return array $uid
    */


    public function getUID($login)
    {
        $query = $this->dbh->prepare("SELECT id FROM {$this->config->table_users} WHERE login = ?");
        $query->execute(array($login));

        if(!$row = $query->fetch(\PDO::FETCH_ASSOC)) {
            return false;
        }

        return $row['id'];
    }

    /**
    * Creates a session for a specified user id
    * @param int $uid
    * @param boolean $remember
    * @return array $data
    */

    protected function addSession($uid, $remember)
    {
        $user = $this->getBaseUser($uid);

        if (!$user) {
            return false;
        }

        $data['hash'] = sha1($this->config->site_key . microtime());

		//если логинится в одном браузере, то в других сессии удаляются
        //$this->deleteExistingSessions($uid);

        if ($remember == true) {
            $data['expire'] = strtotime($this->config->cookie_remember);
        } else {
            $data['expire'] = strtotime($this->config->cookie_forget);
        }

        $data['cookie_crc'] = sha1($data['hash'] . $this->config->site_key);

        $query = $this->dbh->prepare("INSERT INTO {$this->config->table_sessions} (uid, hash, expiredate, cookie_crc) VALUES (?, ?, ?, ?)");

        if (!$query->execute(array($uid, $data['hash'], date("Y-m-d H:i:s", $data['expire']), $data['cookie_crc']))) {
            return false;
        }
		
		setcookie($this->config->cookie_name, $data['hash'], $data['expire']);
        $_COOKIE[$this->config->cookie_name] = $data['hash'];

        return $data;
    }

    /**
    * Removes all existing sessions for a given UID
    * @param int $uid
    * @return boolean
    */

    protected function deleteExistingSessions($uid)
    {
        $query = $this->dbh->prepare("DELETE FROM {$this->config->table_sessions} WHERE uid = ?");
        $query->execute(array($uid));

        return $query->rowCount() == 1;
    }

    /**
    * Removes a session based on hash
    * @param string $hash
    * @return boolean
    */

    protected function deleteSession($hash)
    {
        $query = $this->dbh->prepare("DELETE FROM {$this->config->table_sessions} WHERE hash = ?");
        $query->execute(array($hash));

        return $query->rowCount() == 1;
    }

    /**
    * Function to check if a session is valid
    * @param string $hash
    * @return boolean
    */

    public function checkSession($hash)
    {
        if (strlen($hash) != self::HASH_LENGTH) {
            return false;
        }

        $query = $this->dbh->prepare("SELECT id, uid, expiredate, cookie_crc FROM {$this->config->table_sessions} WHERE hash = ?");
        $query->execute(array($hash));

		if (!$row = $query->fetch(\PDO::FETCH_ASSOC)) {
			return false;
		}

        $sid = $row['id'];
        $uid = $row['uid'];
        $expiredate = strtotime($row['expiredate']);
        $currentdate = strtotime(date("Y-m-d H:i:s"));

        $db_cookie = $row['cookie_crc'];

        if ($currentdate > $expiredate) {
            //$this->deleteExistingSessions($uid);
            $this->deleteSession($hash);

            return false;
        }

        if ($db_cookie == sha1($hash . $this->config->site_key)) {
			if ($expiredate - $currentdate < strtotime($this->config->cookie_renew) - $currentdate) {
                //$this->deleteExistingSessions($uid);
				$this->deleteSession($hash);
                $this->addSession($uid, false);
            }
            return true;
        }
		
        return false;
    }

    /**
    * Retrieves the UID associated with a given session hash
    * @param string $hash
    * @return int $uid
    */

    public function getSessionUID($hash)
    {
        $query = $this->dbh->prepare("SELECT uid FROM {$this->config->table_sessions} WHERE hash = ?");
        $query->execute(array($hash));
		
		if (!$row = $query->fetch(\PDO::FETCH_ASSOC)) {
			return false;
		}

		return $row['uid'];
    }

    protected function getBaseUser($uid)
    {
        $query = $this->dbh->prepare("SELECT email, password, active FROM {$this->config->table_users} WHERE id = ?");
        $query->execute(array($uid));

        $data = $query->fetch(\PDO::FETCH_ASSOC);

        if (!$data) {
            return false;
        }

        $data['uid'] = $uid;

        return $data;
    }

    /**
    * Gets public user data for a given UID and returns an array, password is not returned
    * @param int $uid
    * @return array $data
    */

    public function getUser($uid)
    {
        $query = $this->dbh->prepare("SELECT * FROM {$this->config->table_users} WHERE id = ?");
        $query->execute(array($uid));

        $data = $query->fetch(\PDO::FETCH_ASSOC);

        if (!$data) {
            return false;
        }

        $data['uid'] = $uid;
        unset($data['password']);

        return $data;
    }
	
	/**
    * Gets user data for current user (from cookie) and returns an array, password is not returned
    * @return array $data
    * @return boolean false if no current user
    */
    public function getCurrentUser()
    {
        if ($this->currentuser === NULL) {
            $hash = $this->getSessionHash();
            if ($hash === false) {
                return false;
            }
            $uid = $this->getSessionUID($hash);
            if ($uid === false) {
                return false;
            }
            $this->currentuser = $this->getUser($uid);
        }
        return $this->currentuser;
    }

    /**
    * Returns is user logged in
    * @return boolean
    */
	public function isLogged() {
        if ($this->islogged === NULL) {
            $this->islogged = $this->checkSession($this->getSessionHash());
        }
        return $this->islogged;
    }

    /**
     * Returns current session hash
     * @return string
     */
    public function getSessionHash()
	{
        return isset($_COOKIE[$this->config->cookie_name]) ? $_COOKIE[$this->config->cookie_name] : false;
    }

    /**
     * Compare user's password with given password
     * @param int $userid
     * @param string $password_for_check
     * @return bool
     */
    public function comparePasswords($userid, $password_for_check)
    {
        $query = $this->dbh->prepare("SELECT password FROM {$this->config->table_users} WHERE id = ?");
        $query->execute(array($userid));

        $data = $query->fetch(\PDO::FETCH_ASSOC);

        if (!$data) {
            return false;
        }

        return password_verify($password_for_check, $data['password']);
    }
	
	/**
     * 
     */
	public function restorePassword($email, $password)
	{
		$return['error'] = false;
        
		$query = $this->dbh->prepare("SELECT id, login FROM {$this->config->table_users} WHERE email = ?");
        $query->execute(array($email));
        $user = $query->fetch(\PDO::FETCH_ASSOC);
		
		if($email == '') {
			$return['email'] = 'Обязательное поле';
			$return['error'] = true;
		}
		elseif(!$this->validateEmail($email)) {
			$return['email'] = 'Неверный формат';
			$return['error'] = true;
		}
		elseif(!isset($user['id'])) {
			$return['email'] = 'Такой еmail не найден в системе';
			$return['error'] = true;
		}
		
		if(!$return['error']) {
			
			$password_hash = $this->getHash($password);
			
			$query = $this->dbh->prepare("UPDATE {$this->config->table_users} SET password = ? WHERE id = ?");
			
			if($query->execute(array($password_hash, $user['id']))) {
				
				$subject = 'Восстановление пароля для Orders.LPcopier';
				$message = '<h3 style="font-weight:600;font-size:20px;color:#000000;">Восстановление пароля для Orders.LPcopier!</h3>
					<br>
					<p>Пароль для в логина "' . $user['login'] . '" успешно восстановлен.</p>
					<br>
					<p>Ваши данные для входа в систему:</p>
					<p>Логин: ' . $user['login'] . '<br>Новый пароль: ' . $password . '</p>
					<br><br>
					<p>--<br>С уважением,<br>служба поддержки LPcopier.ru<br>support@lpcopier.ru</p>';
				
				$this->sendMail($email, $subject, $message);
				
			}
			else {
				$return['error'] = true;
			}
		}
		
		return $return;
	}
	
	/**
    * Returns a random string of a specified length
    * @param int $length
    * @return string $key
    */
    public function getRandomKey($length = 32)
    {
        $chars = "A1B2C3D4E5F6G7H8I9J0K1L2M3N4O5P6Q7R8S9T0U1V2W3X4Y5Z6a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6q7r8s9t0u1v2w3x4y5z6";
        $key = "";
        for ($i = 0; $i < $length; $i++) {
            $key .= $chars{mt_rand(0, strlen($chars) - 1)};
        }
        return $key;
    }
	
	/**
    * Login validate
    * @param int $login
    * @return bool
    */
    public function validateLogin($login)
    {
        if(preg_match('/^[a-zA-Z0-9]{3,30}$/', $login)) {
			return true;
		}
		return false;
    }
	
	/**
    * Email validate
    * @param int $login
    * @return bool
    */
    public function validateEmail($email)
    {
		if(preg_match('/^.+@.+\..+$/', $email)) {
			return true;
		}
        return false;
    }
}
