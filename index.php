<?php

/*
 * Basic Settings
 */

include_once 'sources/files.php';

session_start();

define( 'CRAMP_INIT', true );
if( true ){ #debug is false
define( 'CRAMP_BASE', realpath( './testFolder/' ) );
} else {
define( 'CRAMP_BASE', realpath( __DIR__.'/../../../Users/probook 6360b/Downloads/crampBase/' ) );
}
define( 'QUOTA', (1*1024*1024) );

set_error_handler(function( $err ){
	//print_r( $err );
});

#session_unset();

if( empty($_SESSION['PATH']) ){
	$_SESSION['PATH'] = CRAMP_BASE;
	$_SESSION['error'] = '';
	$_SESSION['success'] = '';
	$_SESSION['last_error'] = '';
	$_SESSION['last_success'] = '';

	if( !is_dir(CRAMP_BASE.'/.trash') ){
		mkdir(CRAMP_BASE.'/.trash');
	}
}

$error = $_SESSION['error'];
$success = $_SESSION['success'];

if( !empty($_POST['action']) ){
	$act = strtolower( $_POST['action'] );

	if( $act=='create' && !empty($_POST['folder_name']) ){
		$cr = $_SESSION['PATH'].'/'.$_POST['folder_name'];
		if( !is_dir($cr) ){
			if( false===mkdir($cr) && !is_dir($cr) ){
				$_SESSION['error'] = 'create folder error';
			} else {
				$_SESSION['success'] = 'folder created!';
			}
		} else {
			$_SESSION['error'] = 'Directory exists already';
		}
	} else if( $act=='upload' ){
		$f = $_FILES['files'];
		$n_FILES = array();
		$upload = array();

		foreach ($f['name'] as $key => $value) {
			$n_FILES['file_'.$key] = array(
				'name' => $value,
				'type' => $f['type'][$key],
				'tmp_name' => $f['tmp_name'][$key],
				'error' => $f['error'][$key],
				'size' => $f['size'][$key],
			);

			$upload['file_'.$key] = array();
		}

		$_FILES = $n_FILES;
		$try = File::upload($upload, $_SESSION['PATH'], File::mb(20));

		$success = [];
		foreach ($try as $key => $value) {
			if( !$value[0] ){
				$success[] = '<b>'.$n_FILES[$key]['name'].'</b>: '.$value[1];
			}
		}

		if( empty($success) ) $success[] = 'success';
		die( "<script>window.top.window.endUpload('".implode( '<br/>', $success )."');</script>" );
	}

	elseif ( $act=='search' ) {
		if( !empty($_POST['k']) ){

			$_SESSION['search_key'] = str_replace( '*', '', $_POST['k'] );

		}

		header('Location: .');
	}

}

if( !empty($_GET['action']) ){
	$act = strtolower( $_GET['action'] );

	if ( $act=='download' ) {
		$target_file = realpath( $_SESSION['PATH'].'/'.str_replace( '/', '', $_GET['ent'] ) );
		
		if( file_exists($target_file) && !is_dir($target_file) ){
			header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="'.basename($target_file).'"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($target_file));
            flush();
            readfile($target_file);
            die();
		} else {
			$_SESSION['error'] = 'File does not exists!';
		}
	}

	else if ( $act=='delete' ) {
		$target_file = realpath( $_SESSION['PATH'].'/'.str_replace( '/', '', $_GET['ent'] ) );
		
		if( file_exists($target_file) || is_dir($target_file) ){
			if( !empty($_GET['mode']) && $_GET['mode']=='bin' ){
				$to_file = CRAMP_BASE.'/.trash/'.str_replace( '/', '', $_GET['ent'] );

				if( false === rename( $target_file, $to_file ) ){
					$_SESSION['error'] = 'Could not trash the file';
				} else {
					$_SESSION['success'] = 'File moved to trash successfuly!';
					file_put_contents( $to_file.'.fileinfo' , dirname( $target_file ) );
				}
			} else {
				File::unlink_dir( $target_file );
				unlink( $target_file );
				$_SESSION['success'] = 'File deleted successfuly!';
			}
		} else {
			$_SESSION['error'] = 'File or Directory does not exists!';
		}

        header('Location: .'); die();
	}

	else if ( $act=='rename' && !empty($_GET['name']) ) {
		$target_file = realpath( $_SESSION['PATH'].'/'.str_replace( '/', '', $_GET['ent'] ) );
		$to_file = $_SESSION['PATH'].'/'.$_GET['name'];
		
		if( file_exists($target_file) || is_dir($target_file) ){
			if( false===rename($target_file, $to_file) ){
				$_SESSION['error'] = 'Could not rename file or folder';
			} else {
				$_SESSION['success'] = 'Action completed successfuly!';
			}
		} else {
			$_SESSION['error'] = 'File or Directory does not exists!';
		}

        header('Location: .'); die();
	}

	else if ( $act=='restore' ) {
		$ent = str_replace( '/', '', $_GET['ent'] );
		$target_file = realpath( CRAMP_BASE.'/.trash/'.$ent );
		
		if( file_exists($target_file) || is_dir($target_file) ){
			$to_file = file_get_contents( $target_file.'.fileinfo' )."/$ent";
			if( false===rename( $target_file, $to_file ) ){
				$_SESSION['error'] = 'An error ocurred!';
			} else {
				$_SESSION['success'] = 'File restored!';
				unlink( $target_file.'.fileinfo' );
			}
		} else {
			$_SESSION['error'] = 'File does not exists!';
		}

        header('Location: .'); die();
	}
}

$current_path = $_SESSION['PATH'];

if( !empty($_GET['show']) && $_GET['show']=='trash' ){

	$current_path = CRAMP_BASE.'/.trash';
	$TM = true;

	if( !empty($_GET['do']) && $_GET['do']=='clear' ){
		$s = filesize( $current_path );

		File::unlink_dir( $current_path );
		$_SESSION['success'] = 'Trash cleaned up. Freed up '.$s.' Bytes.';
		mkdir( $current_path );

		header('Location: .');
		die();
	}

} else if( !empty($_GET['ent']) ){
	$ent = str_replace( '/', '', $_GET['ent'] );
	$path = $_SESSION['PATH'].'/'.$ent;

	if( $_GET['ent']=='..' ){
		if( $path!=realpath(CRAMP_BASE.'/../') ){
			$_SESSION['PATH'] = dirname( $_SESSION['PATH'] );
		}
	} else if( "jump"==$_GET['type'] && is_dir($ent) ){
		$_SESSION['PATH'] = $ent;
		$_SESSION['search_key'] = '';
	} else if( is_dir( $path ) ) {
		$_SESSION['PATH'] = $path;
	}

	$_SESSION['LPATH'] = $_GET['ent'];
	header('Location: .');
}

require( 'views/main.php' );

$_SESSION['success'] = '';
$_SESSION['error'] = '';
