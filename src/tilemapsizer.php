<?php
run($argv[1]);

function run($filename) {
	// is this even a tmx file?
	$ext = pathinfo($filename, PATHINFO_EXTENSION);
	if($ext != "tmx") {
		print "Error: not a tmx file.";
		return;
	}

	$name = pathinfo($filename, PATHINFO_FILENAME);
	$format = strrchr($name, "-");

	if($format != "-hd") {
		// make hd
		convertMap($filename, settings_hd());
	}

	if($format != "-ipadhd") {
		// make ipadhd
		convertMap($filename, settings_ipadhd());
	}

	if($format == "-ipadhd" || $format == "-hd") {
		// make ipadhd
		convertMap($filename, settings_sd());
	}
}

function convertMap($filename, $settings) {
	$doc = new DOMDocument;
	$doc->load( $filename );
	$doc->formatOutput = true;
	
	$xpath = new DOMXpath($doc);

	// for each map in the file
	$maps = $xpath->query('//map');
	foreach($maps as $map) {
    		$map->setAttribute('tilewidth', $settings['tilewidth']);
			$map->setAttribute('tileheight', $settings['tileheight']);

		// for each tile set in the mape
		$tilesets = $xpath->query('//tileset', $map);
		foreach($tilesets as $tileset) {
			// update the tileset
			$name = $tileset->getAttribute('name');
			$name = changeCocosExtension($name, $settings['postfix']);
			$tileset->setAttribute('name', $name);
			$tileset->setAttribute('tilewidth', $settings['tilewidth']);
			$tileset->setAttribute('tileheight', $settings['tileheight']);

			// for each image sub element
			$images = $xpath->query('//image', $tileset);
			foreach($images as $image) {
				$source = $image->getAttribute('source');
				$source = changeCocosExtension($source, $settings['postfix']);
				$image->setAttribute('source', $source);
				$image->setAttribute('width', $settings['width']);
				$image->setAttribute('height', $settings['height']);
			}
		}

	}

	$new_filename = changeCocosExtension($filename, $settings['postfix']);
	print "\n new filename:".$new_filename."\n";
	$doc->save($new_filename);
}

function changeCocosExtension($string, $ext) {
	if(preg_match('/(-hd|-ipadhd)/', $string)) {
		// has hd or ipadhd, just replace that
		return preg_replace('/(-hd|-ipadhd)/', $ext, $string);
		// easy, done.
	} else if(preg_match('/(\.tmx|\.gif|\.jpg|\.jpeg|\.png)$/', $string)) {
		// has file extension, take that in to consideration
		return preg_replace('/(\.tmx|\.gif|\.jpg|\.jpeg|\.png)/', $ext.'$1', $string);
	}
	return $string.$ext;
}

function settings_sd() {
	$settings = array();
	$settings['postfix'] = "";
	$settings['tilewidth'] = "48";
	$settings['tileheight'] = "48";
	$settings['width'] = "512";
	$settings['height'] = "512";
	return $settings;
}

function settings_hd() {
	$settings = array();
	$settings['postfix'] = "-hd";
	$settings['tilewidth'] = "96";
	$settings['tileheight'] = "96";
	$settings['width'] = "1024";
	$settings['height'] = "1024";
	return $settings;
}

function settings_ipadhd() {
	$settings = array();
	$settings['postfix'] = "-ipadhd";
	$settings['tilewidth'] = "192";
	$settings['tileheight'] = "192";
	$settings['width'] = "2048";
	$settings['height'] = "2048";
	return $settings;
}

?>