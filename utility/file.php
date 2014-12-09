<?php

class File {

	public static function Upload($array) {
		$f3 = Base::instance();
		extract($array);

		//Handle avatar upload
		$mime = \Web::instance()->mime($name); //Stores file mime type (gets from array above)
		$types = array("image/gif",
					    "image/png",
					    "image/jpeg",);
		
		if(isset($array) && isset($tmp_name) && in_array($type, $types) && in_array($mime, $types)) {
			$directory = getcwd() . '/uploads';
			$name = uniqid(rand(), true).'.'.preg_replace('/^.+[\\\\\\/]/', '', $mime);	//Generates random filename and replaces it
			//Sets permissions of image to read only for all other users
			$destination = $directory . '/' . $name;
			$webdest = '/uploads/' . $name;
			if (move_uploaded_file($tmp_name, $destination)) {
				chmod($destination, 0644);	
				return $webdest;
			} else {
				return false;
			}
		} else{
			return false;
		}
	}
}

?>
