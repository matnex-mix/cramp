<?php

$files = File::dir( $current_path );
array_unshift( $files, '..' );

$folders_html = '';
$files_html = '';

foreach ($files as $key => $value) {
	if( ($value[0]=='.' && $value!='..') || strpos($value, '.fileinfo')!==FALSE ){
		continue;
	}

	$path = $current_path.'/'.$value;
	$p = sha1(md5($value));

	$url = "?ent=".urlencode($value)."&eof=".$p;
	$html = "
<tr id='".$p."' class='@CLASS'>
	<td>
		<input class='item' type='checkbox' name='items[]' value='$value;".$p."' />
	</td>
	<td class='name'>
		<a href='$url' style='word-break: break-all;'>$value</a>
	</td>
	<td>
		<span style='white-space: nowrap;'>
			<mark class='badge'>".Date( 'm/d/Y h:iA', filemtime($path) )."</mark>
		</span>
	</td>
	<td>
		<span style='white-space: nowrap;'>@SIZE</span>
	</td>
	<td>
		<small style=''>@TYPE</small>
	</td>
	<td class='actions' style='white-space: nowrap;'>
		<button onclick='location.href = \"$url&action=download\";' class='d btn btn-primary btn-sm'>
			<i class='fa fa-download fa-sm'></i>
		</button>
		<button class='btn btn-primary btn-sm'>
			<i class='fa fa-copy fa-sm'></i>
		</button>
		<button class='btn btn-primary btn-sm'>
			<i class='fa fa-cut fa-sm'></i>
		</button>
		<button onclick='openDeleteOption(\"$url&action=delete\");' class='btn btn-primary btn-sm'>
			<i class='fa fa-trash fa-sm'></i>
		</button>
		<button onclick='openRenameOption(\"$url&action=rename\", \"$value\");' class='btn btn-primary btn-sm'>
			<i class='fa fa-pencil-alt fa-sm'></i>
		</button>
		<button onclick='location.href = \"$url&action=restore\";' class='t btn btn-primary btn-sm d-none'>
			Restore&nbsp;
			<i class='fa fa-undo fa-sm'></i>
		</button>
	</td>
</tr>
	";

	if( is_dir($current_path.'/'.$value) ){
		$html = str_replace( '@TYPE', 'File folder', $html );
		$html = str_replace( '@SIZE', '', $html );
		$folders_html .= str_replace( '@CLASS', 'folder', $html );
	} else {
		$html = str_replace( '@TYPE', mime_content_type( $path ), $html );
		$html = str_replace( '@SIZE', sizeText( filesize($path) ), $html );
		$files_html .=  str_replace( '@CLASS', 'file', $html );
	}
}

function sizeText( $bytes ) {
	if( $bytes>500*1024*1024 ){
		return round($bytes/1024/1024/1024, 1)."GB";
	} else if( $bytes>500*1024 ){
		return round($bytes/1024/1024, 1)."MB";
	} else if( $bytes>500 ){
		return round($bytes/1024, 1)."KB";
	} else {
		return "${bytes}B";
	}
}

echo $folders_html;
echo $files_html;