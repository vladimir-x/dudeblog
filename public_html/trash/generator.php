<?php 
$page = $_GET['page'];


function includeAndReplace( $filename, $search, $replace){
	$dataRaw = file_get_contents($filename);
	$dataRaw = str_replace($search,$replace,$dataRaw);
	print($dataRaw);
}

/*
include 'empty.html';
//include 'navigator.html';
includeAndReplace('navigator.html',':activeTab',$_GET['filt']);
*/
switch ($page) {
    case 'addblog':  include 'addblog.html'; break;
    case 'blog':     includeAndReplace('blog.html',':filt',$_GET['filt']); break;
    case 'samples':  include 'samples.html'; break;
    case 'about':  	 include 'about.html'; break;
    default: include 'blog.html';
}
/*
include 'footer.html';
*/
?>