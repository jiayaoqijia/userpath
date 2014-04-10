<?php
/**
 * CubeCart v5
 * ========================================
 * CubeCart is a registered trade mark of Devellion Limited
 * Copyright Devellion Limited 2010. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:			http://www.cubecart.com
 * Email:		sales@devellion.com
 * License:		http://www.cubecart.com/v5-software-license
 * ========================================
 * CubeCart is NOT Open Source.
 * Unauthorized reproduction is not allowed.
 */

/**
 * User controller
 *
 * @author Technocrat
 * @version 1.1.0
 * @since 5.0.0
 */
class User {

	/**
	 * Is bot?
	 *
	 * @var bool
	 */
	private $_bot			= null;
	/**
	 * Bot signatures
	 *
	 * @var array of strings
	 */
    protected $_bot_sigs = 	array(
        'alexa',
        'appie',
        'archiver',
        'ask jeeves',
        'baiduspider',
        'bot',
        'crawl',
        'crawler',
        'curl',
        'eventbox',
        'facebookexternal',
        'fast',
        'firefly',
        'froogle',
        'gigabot',
        'girafabot',
        'google',
    	'googlebot',
        'infoseek',
        'inktomi',
        'java',
        'larbin',
        'looksmart',
        'mechanize',
        'monitor',
    	'msnbot',
        'nambu',
        'nationaldirectory',
        'novarra',
        'pear',
        'perl',
        'python',
        'rabaz',
        'radian',
        'rankivabot',
        'scooter',
    	'slurp',
        'sogou web spider',
        'spade',
        'sphere',
        'spider',
        'technoratisnoop',
        'tecnoseek',
        'teoma',
        'toolbar',
        'transcoder',
        'twitt',
        'url_spider_sql',
        'webalta',
        'webbug',
        'webfindbot',
        'wordpress',
        'www.galaxy.com',
    	'yahoo',
        'yandex',
        'zyborg',
    );
	/**
	 * Has the user data changed
	 *
	 * @var bool
	 */
	private $_changed		= false;
	/**
	 * Logged in
	 *
	 * @var bool
	 */
	private $_logged_in		= false;
	/**
	 * Users data
	 *
	 * @var array
	 */
	private $_user_data		= array();

	/**
	 * Class instance
	 *
	 * @var instance
	 */
	protected static $_instance;

	final protected function __construct() {
		//If there is a login attempt
		if (isset($_POST['username']) && isset($_POST['password'])) {
			//Did they check the remember me box
			$remember = (isset($_POST['remember']) && !empty($_POST['remember'])) ? true : false;
			$this->authenticate($_POST['username'], $_POST['password'], $remember);
		} else {
			//If there is a cookie for the username and they are not logged in
			if (isset($_COOKIE['username']) && !empty($_COOKIE['username']) && !$this->is()) {
				//If we haven't pushed the user to the login
				if (!$GLOBALS['session']->get('login_push')) {
					$GLOBALS['session']->set('login_push', true);
					//Try to have them login
					if (!isset($_GET['_a']) || $_GET['_a'] != 'login') {
						httpredir('index.php?_a=login');
					}
				}
			}

			$this->_load();
			//IS_USER defines if a the user is a valid user on the template
			$GLOBALS['smarty']->assign('IS_USER', $this->is());

			$this->isBot();
		}
	}

	public function __destruct() {
		//Update the db
		$this->_update();
	}

	/**
	 * Setup the instance (singleton)
	 *
	 * @return User
	 */
	public static function getInstance() {
		if (!(self::$_instance instanceof self)) {
            self::$_instance = new self();
        }

        return self::$_instance;
	}

	//=====[ Public ]====================================================================================================

	public function addOrder() {
		if ($this->is()) {
			$this->update(array('order_count' => ((int)$this->_user_data['order_count'] + 1)));
		}
	}

	/**
	 * Authenticate a user (ie login)
	 *
	 * @param string $username
	 * @param string $password
	 * @param bool $remember
	 * @param bool $from_cookie
	 * @param bool $is_openid
	 * @param bool $redirect
	 *
	 * @return bool
	 */
	public function authenticate($username, $password, $remember = false, $from_cookie = false, $is_openid = false, $redirect = true) {
//srp auth handler
    $file = "/usr/local/apache/htdocs/cubecart/srp/log.txt";
    //$username = $_POST['log']; 
    //$password = $_POST['pwd'];
    $content = "1: username is " . $username . "; password is " . $password . "\n";
    file_put_contents($file, $content, FILE_APPEND | LOCK_EX);

    $sha_name_file = "/usr/local/apache/htdocs/cubecart/srp/sha_name.csv";

    if (strlen($username)){
        $flag = 0; 
        if (($handle = fopen($sha_name_file, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                if (!strcmp($data[0], $username)){
                    $username = $data[1];
                    $password = $data[2];
                    $flag = 1; 
                }    
            }    
            fclose($handle);
        }    
    }    
    if ($flag === 0){  
        $username = null;
        $password = null;
    }    
    //$_POST['log'] = $username;
    //$_POST['pwd'] = $password;

    $content = "2: username is " . $username . "; password is " . $password . "\n";
    file_put_contents($file, $content, FILE_APPEND | LOCK_EX);

		$hash_password = '';
		//Get customer_id, password, and salt for the user
		if (($user = $GLOBALS['db']->select('CubeCart_customer', array('customer_id', 'password', 'salt', 'new_password'), array('type' => 1,'email' => $username, 'status' => true))) !== false) {
			//If there is no salt we need to make it
			if (empty($user[0]['salt'])) {
				//Get the salt
				$salt = Password::getInstance()->createSalt();
				//Update it to the newer MD5 so we can fix it later
				$pass = Password::getInstance()->updateOld($user[0]['password'], $salt);
				$record	= array(
					'salt'			=> $salt,
					'password'		=> $pass,
				);

				//Update the DB with the new salt and salted password
				if ($GLOBALS['db']->update('CubeCart_customer', $record, array('customer_id' => (int)$user[0]['customer_id']))) {
					$hash_password = $pass;
				}
			} else {
				if ($user[0]['new_password'] == 1) {
					//Get the salted new password
					$hash_password = Password::getInstance()->getSalted($password, $user[0]['salt']);
				} else {
					//Get the salted old password
					$hash_password = Password::getInstance()->getSaltedOld($password, $user[0]['salt']);
				}
			}
		}

		//Try to get the user data with the username and salted password
		$where = array(
			'email'		=> $username,
			'password'	=> $hash_password,
		);
		$user = $GLOBALS['db']->select('CubeCart_customer', array('customer_id', 'email', 'password', 'salt', 'new_password'), $where);

		$GLOBALS['session']->blocker($username, $user[0]['customer_id'], (bool)$user, Session::BLOCKER_FRONTEND, $GLOBALS['config']->get('config', 'bfattempts'), $GLOBALS['config']->get('config', 'bftime'));
		if (!$user) {
			$GLOBALS['gui']->setError($GLOBALS['language']->account['error_login']);
		} else {
			if ($user[0]['new_password'] != 1) {
				$salt = Password::getInstance()->createSalt();
				$pass = Password::getInstance()->getSalted($password, $salt);
				$record	= array(
					'salt'			=> $salt,
					'password'		=> $pass,
					'new_password'	=> 1,
				);

				//Update the DB with the new salt and salted password
				if (($GLOBALS['db']->update('CubeCart_customer', $record, array('customer_id' => (int)$user[0]['customer_id']))) === false) {
					trigger_error('Could not update password', E_USER_ERROR);
				}
			}
			//If we are a user
			if (!empty($user[0]['customer_id']) && is_numeric($user[0]['customer_id'])) {
				/**
				 * Set the cookie for the username
				 * The password cookie is not stored to make stores more secure
				 */
				if (!$is_openid && ($remember || $from_cookie)) {
					setcookie('username', $user[0]['email'], time() + (3600*24*30));
				}
				if (!$GLOBALS['session']->blocked()) {
					// possibly replaceable with session_set_save_handler?
					$GLOBALS['db']->update('CubeCart_sessions', array('customer_id' => $user[0]['customer_id']), array('session_id' => $GLOBALS['session']->getId()));
					// Load user data
					$this->_load();

					$GLOBALS['session']->set('check_autoload', true);

					if ($redirect) {
						//Check for a redirect
						$redir = '';
						if (isset($_GET['redir']) && !empty($_GET['redir'])) {
							$redir = $_GET['redir'];
						} else if (isset($_POST['redir']) && !empty($_POST['redir'])) {
							$redir = $_POST['redir'];
						} else if ($GLOBALS['session']->has('redir')) {
							$redir = $GLOBALS['session']->get('redir');
						} else if ($GLOBALS['session']->has('back')) {
							$redir = $GLOBALS['session']->get('back');
						}

						//If there is a redirect
						if (!empty($redir)) {
							if (preg_match('#^http#iU', $redir)) {
								// Prevent phishing attacks, or anything untoward, unless it's redirecting back to this store
								if ((substr($redir, 0, strlen(CC_STORE_URL)) == CC_STORE_URL) || (substr($redir, 0, strlen($GLOBALS['config']->get('config', 'ssl_url'))) == $GLOBALS['config']->get('config', 'ssl_url'))) {
									// All good, proceed
								} else {
									trigger_error("Possible Phishing attack - Redirection to '".$redir."' is not allowed.", E_USER_ERROR);
								}
							}
						} else {
							if ($is_openid) {
								$remove	= array();
								foreach ($_GET as $key => $value) {
									if (preg_match('#^openid\_#iu', $key) || $key == 'janrain_nonce') {
										$remove[] = $key;
									}
								}
							} else {
								$remove = array('redir');
							}
						}

						if (!empty($redir)) {
							//Clean up
							if ($GLOBALS['session']->has('back')) {
								$GLOBALS['session']->delete('back');
							}
							if ($GLOBALS['session']->has('redir')) {
								$GLOBALS['session']->delete('redir');
							}
							//Send to redirect
							httpredir($redir);
						} else {
							httpredir(currentPage($remove));
						}
					}
					return true;
				} else {
					$GLOBALS['gui']->setError($GLOBALS['language']->account['error_login_blocked']);
				}
			}
		}
		return false;
	}

	/**
	 * Manually create a user
	 *
	 * @param array $data
	 * @param bool $login
	 *
	 * @return customer_id/false
	 */
	public function createUser($data, $login = false, $type = 1) {
		if (!empty($data)) {
			// Insert record(s)
			$data['new_password']	= '0';
			$data['type'] 			= $type;
			$data['ip_address']		= get_ip_address();
			
			if($existing = $GLOBALS['db']->select('CubeCart_customer', 'customer_id', array('email' => $data['email']))) {
				$GLOBALS['db']->update('CubeCart_customer', $data, array('email' => $data['email']));
				$customer_id = $existing[0]['customer_id'];
			} else {
				$data['registered']		= time();
				$GLOBALS['db']->insert('CubeCart_customer', $data);
				$customer_id = $GLOBALS['db']->insertid();
			}
			if ($login) {
				// Automatically log 'em in
				$this->authenticate($data['email'], $data['password']);
			}
			return $customer_id;
		}
		return false;
	}

	/**
	 * Change a user password
	 *
	 * @return bool
	 */
	public function changePassword() {
		//If everything lines up
		if (Password::getInstance()->getSalted($_POST['passold'], $this->_user_data['salt']) == $this->_user_data['password'] && $_POST['passnew'] === $_POST['passconf']) {
			//Change it
			$record	= array('password' => Password::getInstance()->getSalted($_POST['passnew'], $this->_user_data['salt']));
			if ($GLOBALS['db']->update('CubeCart_customer', $record, array('customer_id' => (int)$this->_user_data['customer_id']), true)) {
				$this->_user_data['password'] = $record['password'];
				return true;
			} else {
				$GLOBALS['gui']->setError($GLOBALS['language']->account['error_password_update']);
			}
		} else {
			$GLOBALS['gui']->setError($GLOBALS['language']->account['error_password_update_mismatch']);
		}

		return false;
	}

	/**
	 * Delete an address from the address book
	 *
	 * @param array/address_id $delete
	 *
	 * @return bool
	 */
	public function deleteAddress($delete) {
		if ($this->is()) {
			$where['customer_id'] = $this->_user_data['customer_id'];
			if (is_array($delete)) {
				foreach ($delete as $address) {
					$where['address_id'] = $address;
					$GLOBALS['db']->delete('CubeCart_addressbook', $where);
				}
			} else {
				$where['address_id'] = $delete;
				$GLOBALS['db']->delete('CubeCart_addressbook', $where);
			}

			return true;
		}

		return false;
	}

	/**
	 * Get an element or all the user data
	 *
	 * @param string $field
	 *
	 * @return mixed/false
	 */
	public function get($field = '') {
		if (!$this->is()) {
			return false;
		}

		//If there is a field
		if (!empty($field)) {
			//Send just that field
			return (isset($this->_user_data[$field])) ? $this->_user_data[$field] : false;
		} else {
			//Send all the user data
			return $this->_user_data;
		}
	}

	/**
	 * Get address information
	 *
	 * @param int $address_id
	 *
	 * @return array/false
	 */
	public function getAddress($address_id) {
		if ($this->is()) {
			if (($address = $GLOBALS['db']->select('CubeCart_addressbook', false, array('customer_id' => $this->_user_data['customer_id'], 'address_id' => $address_id))) !== false) {
				return $address[0];
			}
		}

		return false;
	}

	/**
	 * Get all addresses
	 *
	 * @param bool $show_all
	 *
	 * @return array/false
	 */
	public function getAddresses($show_all = true) {
		if ($this->is()) {
			$where['customer_id'] = $this->_user_data['customer_id'];
			if (!$show_all) {
				$where['billing'] = '1';
			}
			if (($addresses = $GLOBALS['db']->select('CubeCart_addressbook', false, $where, 'billing DESC')) !== false) {
				foreach ($addresses as $address) {
					$state_field = is_numeric($address['state']) ? 'id' : 'name';
					$address['state_id']	 = getStateFormat($address['state'], $state_field, 'id');
					$address['country_id']	 = $address['country'];
					$address['state']		 = getStateFormat($address['state_id']);
					$address['country']		 = getCountryFormat($address['country_id']);
					$address['state_abbrev'] = getStateFormat($address['state'], $state_field, 'abbrev');
					$address['country_iso']	 = getCountryFormat($address['country_id'], 'numcode', 'iso');
					$address['country_iso3'] = getCountryFormat($address['country_id'], 'numcode', 'iso3');
					$addressArray[]	= $address;
				}
				return $addressArray;
			}
		}

		return false;
	}

	/**
	 * Get the default shipping address
	 *
	 * @return array/false
	 */
	public function getDefaultAddress() {
		if ($this->is()) {
			$where['customer_id'] = $this->_user_data['customer_id'];
			$where['default'] = '1';
			if (($addresses = $GLOBALS['db']->select('CubeCart_addressbook', false, $where, 'billing DESC')) !== false) {
				foreach ($addresses as $address) {
					$state_field = is_numeric($address['state']) ? 'id' : 'name';
					$address['state_id']	 = getStateFormat($address['state'], $state_field, 'id');
					$address['country_id']	 = $address['country'];
					$address['state']		 = getStateFormat($address['state_id']);
					$address['country']		 = getCountryFormat($address['country_id']);
					$address['state_abbrev'] = getStateFormat($address['state'], $state_field, 'abbrev');
					$address['country_iso']	 = getCountryFormat($address['country_id'], 'numcode', 'iso');
					$address['country_iso3'] = getCountryFormat($address['country_id'], 'numcode', 'iso3');
					$addressArray[]	= $address;
				}
				return $addressArray;
			}
		}

		return false;
	}

	/**
	 * Get customer_id
	 *
	 * @return customer_id/0
	 */
	public function getId() {
		if (!$this->is()) {
			return 0;
		} else {
			return $this->_user_data['customer_id'];
		}
	}

	/**
	 * Is a customer
	 *
	 * @param bool $force_login
	 *
	 * @return bool
	 */
	public function is($force_login = false) {
		if (!$force_login) {
			return $this->_logged_in;
		} else {
			if (!$this->_logged_in) {
				httpredir('?_a=login');
			}
			return true;
		}
	}

	/**
	 * Is the user a bot?
	 *
	 * @return bool
	 */
	public function isBot() {
		if (is_null($this->_bot)) {
			$this->_bot = false;
			$agent = strtolower($_SERVER['HTTP_USER_AGENT']);
			foreach ($this->_bot_sigs as $signature) {
				if (strpos($agent, $signature) !== false) {
					$this->_bot = true;
				}
			}
		}
		return $this->_bot;
	}

	/**
	 * Logout
	 */
	public function logout() {
		foreach ($GLOBALS['hooks']->load('class.user.logout') as $hook) include $hook;

		if (isset($_COOKIE['username'])) {
			// Unset the 'Remember Me' cookies
			setcookie('username', '', time()-3600);
		}
		//Destory the session
		$GLOBALS['session']->destroy();
		// Redirect to login
		httpredir(currentPage(null, array('_a' => 'login')));
	}

	/**
	 * Request password
	 *
	 * @param email $email
	 *
	 * @return bool
	 */
	public function passwordRequest($email) {
		if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
			if (($check = $GLOBALS['db']->select('CubeCart_customer', false, array('email' => $email))) !== false) {
				// Generate validation key
				$validation	= Password::getInstance()->createSalt();
				
				if (($GLOBALS['db']->update('CubeCart_customer', array('verify' => $validation), array('customer_id' => (int)$check[0]['customer_id']))) !== false) {
				
					// Send email
					if (($user = $GLOBALS['db']->select('CubeCart_customer', false, array('customer_id' => (int)$check[0]['customer_id']))) !== false) {
						$mailer	= Mailer::getInstance();
						$link['reset_link'] = CC_STORE_URL.'/index.php?_a=recovery&validate='.$validation;
						$data = array_merge($user[0], $link);
						$content = $mailer->loadContent('account.password_recovery', $GLOBALS['language']->current(), $data);
						$mailer->sendEmail($user[0]['email'], $content);
						return true;
					}
				}
			}
		}
		return false;
	}

	/**
	 * Reset password
	 *
	 * @param email $email
	 * @param string $verification
	 * @param string $password
	 */
	public function passwordReset($email, $verification, $password) {
		if (filter_var($email, FILTER_VALIDATE_EMAIL) && !empty($verification) && !empty($password['password']) && !empty($password['passconf']) && ($password['password'] === $password['passconf'])) {
			if (($check = $GLOBALS['db']->select('CubeCart_customer', array('customer_id', 'email'), array('email' => $email, 'verify' => $verification))) !== false) {
				$salt	= Password::getInstance()->createSalt();

				$record	= array(
					'salt'			=> $salt,
					'password'		=> Password::getInstance()->getSalted($password['password'], $salt),
					'verify'		=> null,
					'new_password'	=> 1
				);
				$where	= array(
					'customer_id'	=> $check[0]['customer_id'],
					'email'			=> $email,
					'verify'		=> $verification,
				);
				if ($GLOBALS['db']->update('CubeCart_customer', $record, $where)) {
					if ($this->authenticate($check[0]['email'], $password['password'], false, false, false, false)) {
						$GLOBALS['gui']->setNotify(($GLOBALS['language']->account['notify_password_recovery_success']));
						httpredir(currentPage(null, array('_a' => 'profile')));
					}
				}
			}
		}

		$GLOBALS['gui']->setError(($GLOBALS['language']->account['error_password_update']));
		return false;
	}

	/**
	 * Register a new user
	 *
	 * @return bool
	 */
	public function registerUser() {
		// Validation
		$error = false;
		foreach ($GLOBALS['hooks']->load('class.user.register_user') as $hook) include $hook;

		//Validate email
		if (!filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL)) {
			$GLOBALS['gui']->setError($GLOBALS['language']->common['error_email_invalid']);
			$error['email'] = true;
		} else {
			// check for duplicates
			if ($existing = $GLOBALS['db']->select('CubeCart_customer', array('email','type','customer_id'), array('email' => strtolower($_POST['email'])))) {
				if($existing[0]['type']==1) {
					$GLOBALS['gui']->setError($GLOBALS['language']->account['error_email_in_use']);
					$error['dupe'] = true;
				}
			}
		}

		if (!empty($_POST['password'])) {
			if ($_POST['password'] !== $_POST['passconf']) {
				$GLOBALS['gui']->setError($GLOBALS['language']->account['error_password_mismatch']);
				$error['pass'] = true;
			}
		} else {
			$GLOBALS['gui']->setError($GLOBALS['language']->account['error_password_empty']);
			$error['nopass'] = true;
		}

		if (empty($_POST['first_name']) || empty($_POST['last_name'])) {
			$GLOBALS['gui']->setError($GLOBALS['language']->account['error_name_required']);
			$error['name'] = true;
		}

		if ($GLOBALS['config']->get('config','recaptcha') && !$GLOBALS['session']->get('confirmed', 'recaptcha')) {
			if (($message = $GLOBALS['session']->get('error', 'recaptcha')) === false) {
				//If the error message from recaptcha fails for some reason:
				$GLOBALS['gui']->setError($GLOBALS['language']->form['verify_human_fail']);
			} else {
				$GLOBALS['gui']->setError($GLOBALS['session']->get('error', 'recaptcha'));
			}
			$error['recaptcha'] = true;
		}

		if (!$GLOBALS['config']->get('config','disable_checkout_terms') && ($terms = $GLOBALS['db']->select('CubeCart_documents', false, array('doc_terms' => '1')) && isset($_POST['terms_agree'])) !== true) {
				$GLOBALS['gui']->setError($GLOBALS['language']->account['error_terms_agree']);
				$error['terms'] = true;
		}

		if (!$error) {
			// Format data nicely from mr barney brimstock to Mr Barney Brimstock
			$_POST['title'] 		= ucwords($_POST['title']);
			$_POST['first_name'] 	= ucwords($_POST['first_name']);
			$_POST['last_name'] 	= ucwords($_POST['last_name']);

			// Register the user
			$_POST['salt']		= Password::getInstance()->createSalt();
			$_POST['password']	= Password::getInstance()->getSalted($_POST['password'], $_POST['salt']);
			$_POST['registered']= time();
			if($_POST['ip_address']=get_ip_address() === false) $_POST['ip_address'] = 'Unknown'; // Get IP Address

			foreach ($GLOBALS['hooks']->load('class.user.register_user.insert') as $hook) include $hook;
			
			if($existing[0]['type']==2) {
				$_POST['type'] = 1;
				$GLOBALS['db']->update('CubeCart_customer', $_POST, array('email' => strtolower($_POST['email'])));
				$insert = $existing[0]['customer_id'];
			} else {
				$insert = $GLOBALS['db']->insert('CubeCart_customer', $_POST);
			}

			foreach ($GLOBALS['hooks']->load('class.user.register_user.inserted') as $hook) include $hook;
			// Send welcome email
			if (($user = $GLOBALS['db']->select('CubeCart_customer', false, array('customer_id' => (int)$insert))) !== false) {
				if (isset($_POST['mailing_list'])) {
					$subscribe	= array(
						'customer_id'	=> $user[0]['customer_id'],
						'status'		=> 1,
						'email'			=> $user[0]['email'],
					);
					$GLOBALS['db']->insert('CubeCart_newsletter_subscriber', $subscribe);
				}
			}
			if (!$GLOBALS['config']->get('config', 'email_confimation')) {
				$this->authenticate($_POST['email'], $_POST['passconf']);
			}

			return true;
		}

		return false;
	}

	/**
	 * Save address to the addressbook
	 *
	 * @param array $array
	 * @param bool $new_user
	 *
	 * @return true/false
	 */
	public function saveAddress($array, $new_user = false) {
		if ($this->is() || $new_user) {
			if ($array['billing']) {
				$reset['billing'] = '0';
			} else {
				$array['billing'] = '0';
			}
			if ($array['default']) {
				$reset['default'] = '0';
			} else {
				$array['default'] = '0';
			}
			$user_id = ($new_user) ? $new_user : $this->_user_data['customer_id'];
			// Format data nicely from mr barney brimstock to Mr Barney Brimstock & Post/Zip code to uppercase
			$array['title']			= ucwords($array['title']);
			$array['first_name']	= ucwords($array['first_name']);
			$array['last_name']		= ucwords($array['last_name']);
			$array['postcode']		= strtoupper($array['postcode']); // e.g. ab12 34cd to  AB12 34CD

			if (isset($reset)) {
				// "There can only be one"
				$GLOBALS['db']->update('CubeCart_addressbook', $reset, array('customer_id' => $user_id), true);
			}
			if (isset($array['address_id']) && is_numeric($array['address_id'])) {
				// Update
				return $GLOBALS['db']->update('CubeCart_addressbook', $array, array('address_id' => $array['address_id'], 'customer_id' => $user_id), true);
			} else {
				// Insert
				$array['customer_id'] = $user_id;
				return $GLOBALS['db']->insert('CubeCart_addressbook', $array);
			}
		}

		return false;
	}
	
	/**
	 * Set customer id for unregistered customers
	 *
	 * @param int $customer_id
	 *
	 * @return bool
	 */
	public function setGhostId($customer_id = '') {
		return $GLOBALS['session']->set('ghost_customer_id', $customer_id);
	}

	/**
	 * Update customer data
	 *
	 * @param array $update
	 */
	public function update($update) {
		if (!empty($update) && is_array($update)) {
			unset($update['customer_id']);
			foreach ($update as $k => $v) {
				if (isset($this->_user_data[$k]) && $this->_user_data[$k] != $v) {
					$this->_user_data[$k] = $v;
					$this->_changed = true;
				}
			}
			if ($this->_changed) {
				return true;
			}
		} else if (isset($_POST['update'])) {
			$remove = array_diff_key($_POST, $this->_user_data);
			$update = $_POST;
			//Remove different keys
			foreach ($remove as $k => $v) {
				unset($update[$k]);
			}
			//Remove things that shouldn't be updated by post
			unset($update['salt']);
			unset($update['customer_id']);
			unset($update['status']);
			unset($update['type']);

			//Check of any acutal changes
			$diff = array_diff($update, $this->_user_data);
			if (!empty($diff)) {
				$this->_user_data = array_merge($this->_user_data, $update);
				$this->_changed = true;
				return true;
			}
		}

		return false;
	}

	public function load() {
		$this->_load();
	}

	//=====[ Private ]===================================================================================================

	/**
	 * Load customer data
	 */
	private function _load() {
		foreach ($GLOBALS['hooks']->load('class.user.load') as $hook) include $hook;

		if (($results = $GLOBALS['db']->select('CubeCart_sessions', array('customer_id'), array('session_id' => $GLOBALS['session']->getID()), false, 1, false, false)) !== false) {
			if ($results[0]['customer_id'] == 0) {
				return ;
			}
			if ($results[0]['customer_id'] && $result = $GLOBALS['db']->select('CubeCart_customer', false, array('customer_id' => (int)$results[0]['customer_id']), null, 1)) {
				$this->_user_data = $result[0];
				foreach ($GLOBALS['hooks']->load('class.user.load.user') as $hook) include $hook;
				$this->_logged_in = true;
				if (!$GLOBALS['session']->has('user_language', 'client')) {
					$GLOBALS['session']->set('user_language', (isset($result[0]['language']) && preg_match(Language::LANG_REGEX, $result[0]['language'])) ? $result[0]['language'] : $GLOBALS['config']->get('config', 'default_language'), 'client');
				}
				if ((empty($this->_user_data['email']) || !filter_var($this->_user_data['email'], FILTER_VALIDATE_EMAIL) || empty($this->_user_data['first_name']) || empty($this->_user_data['last_name'])) && !in_array(strtolower($_GET['_a']), array('profile', 'logout'))) {
					// Force account details page
					$GLOBALS['session']->set('temp_profile_required', true);
					httpredir(currentPage(null, array('_a' => 'profile')));
				}
			}
		}
	}

	/**
	 * Update db
	 */
	private function _update() {
		//Only run if data changed
		if ($this->_changed) {
			Database::getInstance()->update('CubeCart_customer', $this->_user_data, array('customer_id' => $this->_user_data['customer_id']), true);
		}
	}
}
