<?php

/**
 * Script for building the single file version
 */
$files = [
	'css' => 'style.css',
	'jszip' => 'js/jszip.min.js',
	'papaparse' => 'js/papaparse.min.js',
	'script' => 'js/script.js'
];

function output(string $text)
{
	echo $text . " \n";
}

function loadfile($path): string
{
	$page = file_get_contents($path);

	if ($page === false) {
		throw new Exception('Failed to load file:' . $path);
	}

	return $page;
}

function addInlineParts($page, $files): string
{
	foreach ($files as $id => $file) {
		$data = loadfile('public/' . $file);

		if ($id === 'css') {
			$data = '<style>' . $data . '</style>';

		} else {
			$data = '<script>' . $data . '</script>';
		}

		$page = str_replace('{'. $id .'}', $data, $page);
	}

	return $page;
}

function addExternalParts($page, $files): string
{
	foreach ($files as $id => $file) {
		if ($id === 'css') {
			$data = '<link rel="stylesheet" type="text/css" href="'. $file .'" />';

		} else {
			$data = '<script src="'. $file .'" type="text/javascript"></script>';
		}

		$page = str_replace('{'. $id .'}', $data, $page);
	}

	return $page;
}

function addBuildDetails(string $page): string
{
	exec('git rev-parse --verify HEAD', $output);
	$hash = $output[0];

	$page = str_replace(['{date}', '{commit}'], [date('c'), $hash], $page);
	return $page;
}

function save(string $page, string $path)
{
	$status = file_put_contents($path, $page);

	if ($status === false) {
		throw new Exception('Failed to save file: ' . $path);
	}

	output('Create file: ' . $path);
}

try {
	// All one version
	$page = loadfile('templates/twMediaViewer.html');
	$page = addInlineParts($page, $files);
	$page = addBuildDetails($page);
	save($page, 'public/twMediaViewer.html');

	// Main website version
	$page = loadfile('templates/twMediaViewer.html');
	$page = addExternalParts($page, $files);
	$page = addBuildDetails($page);
	save($page, 'public/index.html');

	output('Done.');
} catch(Exception $e) {
	output($e->getMessage());
	exit(1);
}
