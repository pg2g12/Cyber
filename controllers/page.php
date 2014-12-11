<?php

class Page extends Controller {

	function display($f3) {
		$pagename = urldecode($f3->get('PARAMS.3'));
		$page = $this->Model->Pages->fetch($pagename);
		if(is_string($page) && strlen($pagename) !=0){	//If the name contains a hash it will be equivilant to 0
			$pagetitle = ucfirst(str_replace("_"," ",str_replace(".html","",$pagename)));
			$f3->set('pagetitle',$pagetitle);
			$f3->set('page',$page);
		}
		else {
			StatusMessage::add('Nothing to see here...','danger');
			$f3->reroute('/');
		}
		
	}
	
}

?>
