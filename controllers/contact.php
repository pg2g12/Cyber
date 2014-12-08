<?php

class Contact extends Controller {

	public function index($f3) {
		if($this->request->is('post')) {
			extract($this->request->data);

			if (filter_var($from, FILTER_VALIDATE_EMAIL)) {

			$from = "From: $from";
			mail($to,$subject,$message,$from);
			StatusMessage::add('Thank you for contacting us');

			} else {

			StatusMessage::add('Invalid email','danger');
			}
			return $f3->reroute('/');
		}	
	}

}

?>