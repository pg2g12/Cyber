<?php
class User extends Controller {
	
	public function view($f3) {
		$userid = $f3->get('PARAMS.3');
		$u = $this->Model->Users->fetch($userid);

		$articles = $this->Model->Posts->fetchAll(array('user_id' => $userid));
		$comments = $this->Model->Comments->fetchAll(array('user_id' => $userid));

		$f3->set('u',$u);
		$f3->set('articles',$articles);
		$f3->set('comments',$comments);
	}

	public function add($f3) {
		if($this->request->is('post')) {
			extract($this->request->data);
			$check = $this->Model->Users->fetch(array('username' => $username));
			if (!empty($check)) {
				StatusMessage::add('User already exists','danger');
			} else if($password != $password2) {
				StatusMessage::add('Passwords must match','danger');
			} else if ($captcha == $_SESSION['captcha_code']) {
				$user = $this->Model->Users;
				$user->copyfrom('POST');
				$user->password = sha1($user->password);
				$user->created = mydate();
				$user->bio = '';
				$user->level = 1;
				if(empty($displayname)) {
					$user->displayname = $user->username;
				}
				$user->save();	
				StatusMessage::add('Registration complete','success');
				return $f3->reroute('/user/login');
			} else {
				StatusMessage::add('Captcha does not match, please try again','danger');
			}
		}
	}

	public function login($f3) {
		if ($this->request->is('post')) {
			list($username,$password) = array($this->request->data['username'],$this->request->data['password']);
			if ($this->request->data['captcha'] == $_SESSION['captcha_code']) {
				if ($this->Auth->login($username, sha1($password))) {
					StatusMessage::add('Logged in succesfully','success');
					$loc = preg_replace('/[^a-zA-Z0-9\/\-]/', '', $_GET['from']);	//Filter to add protection from open redirects
					if(isset($loc)) {
						$f3->reroute($loc);
					} else {
						$f3->reroute('/');
					}
				} else {
					StatusMessage::add('Incorrect username or password','danger');
				}
			} else {
				StatusMessage::add('Invalid captcha','danger');
			}
		}		
	}

	public function logout($f3) {
		$this->Auth->logout();
		StatusMessage::add('Logged out succesfully','success');
		$f3->reroute('/');	
	}


	public function profile($f3) {	
		$id = $this->Auth->user('id');
		extract($this->request->data);
		$u = $this->Model->Users->fetch($id);
		$oldpass = $u->password;
		if($this->request->is('post')) {
			$u->copyfrom('POST');
			if(empty($u->password)) { 
				$u->password = $oldpass; 
			}
			else { 
			$u->password = sha1($u->password); //Encrypt password using sha1
			}

			//Handle avatar upload
			if(isset($_FILES['avatar']) && isset($_FILES['avatar']['tmp_name']) && !empty($_FILES['avatar']['tmp_name'])) {
				$url = File::Upload($_FILES['avatar']);
				if (!$url){
					$fail  = '';
					\StatusMessage::add('Profile update failed (Check image type is correct)','danger');
				}else{
					$u->avatar = $url;
				}
				
			} else if(isset($reset)) {
				$u->avatar = '';
			}
			
			$u->save();
			if(!isset($fail)){
				\StatusMessage::add('Profile updated sucesfully','success');
			}
			
			return $f3->reroute('/user/profile');
		}			
		$_POST = $u->cast();
		$f3->set('u',$u);
	}

	public function promote($f3) {
		$id = $this->Auth->user('id');
		$u = $this->Model->Users->fetch($id);
		$u->level = 2;
		$u->save();
		return $f3->reroute('/');
	}

}
?>
