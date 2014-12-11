<?php

	class AuthHelper {

		/** Construct a new Auth helper */
		public function __construct($controller) {
			$this->controller = $controller;
		}

		/** Attempt to resume a previously logged in session if one exists */
		public function resume() {
			$f3=Base::instance();				

			//Ignore if already running session	
			if($f3->exists('SESSION.user.id')) return;

			//Log user back in from cookie
			if($f3->exists('COOKIE.RobPress_User')) {
				$db = $this->controller->db;
				$user = $db->connection->exec("SELECT * FROM `users` WHERE `cookiez`=:cookie",
				array(':cookie'=>$f3->get('COOKIE.RobPress_User')));	//Return the user corresponding to the cookie
				$this->forceLogin($user[0]);	//Force login the first result returned in the array
			}
		}		

		/** Look up user by username and password and log them in */
		public function login($username,$password) {
			$f3=Base::instance();						
			$db = $this->controller->db;
			//Parameterized query for login
			$results = $db->connection->exec("SELECT * FROM `users` WHERE `username`=:uName AND `password`=:passWd",
				array(':uName'=>$username, ':passWd'=>$password));

			if (!empty($results)) {		
				$user = $results[0];	
				$this->setupSession($user, $db);
				return $this->forceLogin($user);
			} 
			return false;
		}

		/** Log user out of system */
		public function logout() {
			$f3=Base::instance();							

			//Kill the session
			session_destroy();

			//Kill the cookie
			setcookie('RobPress_User','',time()-3600,'/');
		}

		/** Set up the session for the current user */
		public function setupSession($user, $db) {
			//Remove previous session
			session_destroy();

			//Setup new session
			session_id(sha1(rand()));	//Session is a nolonger using user ID and is more secure

			//Setup cookie for storing user details and for re-logging in
			$cookiez = sha1(uniqid(rand()));	//Set cookie variable for reference later, also more secure then previous base_64
			setcookie('RobPress_User',$cookiez,time()+3600*24*30,'/');

			$db->connection->exec("UPDATE `users` SET `cookiez`=:cookiez WHERE `username`=:uName",
				array(':cookiez'=>$cookiez,':uName'=>$user['username']));	//Put cookie into database
				
			//And begin!
			new Session();
		}

		/** Not used anywhere in the code, for debugging only */
		public function specialLogin($username) {
			//YOU ARE NOT ALLOWED TO CHANGE THIS FUNCTION
			$f3 = Base::instance();
			$user = $this->controller->Model->Users->fetch(array('username' => $username));
			$array = $user->cast();
			return $this->forceLogin($array);
		}

		/** Force a user to log in and set up their details */
		public function forceLogin($user) {
			//YOU ARE NOT ALLOWED TO CHANGE THIS FUNCTION
			$f3=Base::instance();						
			$f3->set('SESSION.user',$user);
			return $user;
		}

		/** Get information about the current user */
		public function user($element=null) {
			$f3=Base::instance();
			if(!$f3->exists('SESSION.user')) { return false; }
			if(empty($element)) { return $f3->get('SESSION.user'); }
			else { return $f3->get('SESSION.user.'.$element); }
		}

	}

?>
