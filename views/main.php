<!DOCTYPE html>
<html style='overflow-x: hidden'>
	<head>
		<meta charset='UTF-8'>
		<meta name='viewport' content='width=device-width,initial-scale=1.0' />
		<link rel='icon' href='favicon.png' />

		<link rel='stylesheet' href='minify/bootstrap.min.css' type='text/css' />
		<link rel='stylesheet' href='minify/fontawesome5/css/all.min.css' type='text/css' />
		<link rel='stylesheet' href='assets/css/app.css' type='text/css' />
		
		<script src='minify/jquery.min.js'></script>
		<script src='minify/bootstrap.bundle.min.js'></script>
		<script src='assets/js/app.js'></script>

		<title>Cramp File Manager</title>
	</head>
	<body id="body" style="background: #eee">
		<br/><br/>
		<div class="d-flex align-items-center mw-100 px-3 px-md-5 position-sticky" style="top: 30px; z-index: 1000;">
			<!--a href="?" class="btn p-0 link" >
				<img src="favicon.png" width="27" />
				<em style="font-family: 'Arial Rounded MT Bold'; color: tomato; font-size: large;">&nbsp;
					<b>Cramp</b>
				</em>
			</a-->

			<form method="post" class="flex-grow-1 mr-3 mr-md-5 position-relative">
				<input type="search" name="k" placeholder="Search..." class="form-control form-control-lg bg-dark shadow-sm border-0 w-100" value="<?php if( false!== ($p = $_SESSION['search_key']) ){ echo substr($p, strlen($p)-1); } ?>" />

				<button type="submit" name="action" value="search" class="btn position-absolute" style="top: 4px; right: 10px;">
					<i class="fa fa-search fa-sm text-light"></i>
				</button>
			</form>

			<button data-toggle="dropdown" data-target="menu" class="btn btn-white">
				<i class="fa fa-ellipsis-v"></i>
			</button>
			<div id="menu" class="dropdown-menu dropdown-menu-right border-0 rounded-0 shadow-sm">
				<a href="" data-toggle="modal" data-target="#container" class="dropdown-item" >
					<i class="fa fa-file-upload fa-sm"></i>
					&nbsp;&nbsp;
					Upload File
				</a>
				<a href="" data-toggle="modal" data-target="#add-folder-container" class="dropdown-item" >
					<i class="fa fa-folder fa-sm"></i>
					&nbsp;&nbsp;
					New Folder
				</a>
				<div class="dropdown-divider"></div>
				<a href="?show=trash" class="dropdown-item" >
					<i class="fa fa-trash fa-sm"></i>
					&nbsp;&nbsp;
					Trash
				</a>
				<a href="?show=trash&do=clear" onclick="if( prompt('Confirm this action by typing (\'CLEAR\') in the box.\n Kindly note this action is irreversible!') != 'CLEAR' ){ event.preventDefault(); }" class="dropdown-item" >
					<i class="fa fa-times fa-sm"></i>
					&nbsp;&nbsp;
					Clear Trash
				</a>
			</div>
		</div>
		<br/><br/>

		<div class="col-12">
			<div class="bg-white shadow-sm p-3" style="overflow-y: auto; overflow-x: hidden;">
				<div class="table-responsive">
					<p style="white-space: nowrap;">
						<strong>Current Directory: &nbsp;&nbsp;</strong>
						<span id="cpath"><?php echo realpath( $current_path ); ?></span>
					</p>

					<table class="table mt-3">
						<tr>
							<th>
								<input type="checkbox" onclick="$('.item').prop( 'checked', this.checked );" />
							</th>
							<th>
								Name
							</th>
							<th>
								Date
							</th>
							<th>
								Size
							</th>
							<th>
								Type
							</th>
							<th>
								Actions
							</th>
						</tr>
						<?php

							if( !empty($_SESSION['search_key']) ) {
								$_SESSION['search_key'] .= "*";
								include('search-listing.php');

								?>
								
								<script type="text/javascript">
									$('.actions>*:not(.d)').remove();
									$('#cpath').html('Search Results');
								</script>

								<style type="text/css">
									.table tr:nth-child(2) {
										display: none;
									}
								</style>

								<?php
							}
							else {
								include('listing.php');
							}

						?>
					</table>
				</div>
			</div>
		</div>

		<form autocomplete="off" method="post" enctype="multipart/form-data" target="upload_iframe" id="container" class="modal modal-show fade">
			<div class="modal-dialog modal-dialog-centered modal-xl">
				<div class="modal-content rounded-0 border-0">
					<div class="modal-body" style="height: 600px; max-height: 600px;">
						<div id="upload" class="h-100 mh-100" style="overflow-y: auto;">
							<div class="h-100 d-flex align-items-center justify-content-center" style="border: 5px dashed lightgrey;">
								<div class="text-center">
									<h5>Drop files here to upload!</h5>
									<input id="files" name="files[]" class="d-none" type="file" onchange="startUpload();" multiple />
									<input type="hidden" name="action" value="upload" />
									<button type="button" onclick="$('#files').click();" class="btn btn-dark mt-4" >
										UPLOAD
									</button>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</form>
		<iframe id="upload_iframe" name="upload_iframe" src="#" style="width: 0; height: 0;"></iframe>
		<div id="upload_loader" class="fixed-top h-100 d-none align-items-center justify-content-center" style="background-color: rgba(0,0,0,0.3); z-index: 9999999;">
			<div class="spinner-grow" role="status" style="height: 4rem; width: 4rem;">
			  	<span class="sr-only">Loading...</span>
			</div>
		</div>

		<form autocomplete="off" method="post" id="add-folder-container" class="modal modal-show fade">
			<div class="modal-dialog modal-dialog-centered modal-md">
				<div class="modal-content">
					<div class="modal-body py-5 px-md-5">
						<p>Supply the folder name which can contain (A-Za-z.~)</p>
						<input placeholder="Folder Name" name="folder_name" class="form-control" />
						<br/>
						<input type="submit" class="btn btn-danger w-100" name="action" value="CREATE" />
					</div>
				</div>
			</div>
		</form>

		<div id="delete-container" class="modal modal-show fade">
			<div class="modal-dialog modal-dialog-centered modal-sm">
				<div class="modal-content border-0">
					<div class="modal-body p-4">
						<p>Would like to delete the file permanently?</p>
						<div class="d-flex justify-content-between mt-4">
							<button class="btn btn-danger" onclick="if( confirm('Are you sure you want to permanently delete this file?') ){ location.href = window.currentIDL; } else { $('#delete-container').modal('hide'); }">YES</button>
							<span>&nbsp;&nbsp;&nbsp;</span>
							<button class="btn btn-primary" onclick="location.href = window.currentIDL+'&mode=bin';">NO</button>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div id="rename-container" class="modal modal-show fade">
			<div class="modal-dialog modal-dialog-centered modal">
				<div class="modal-content border-0">
					<div class="modal-body py-5 px-md-5">
						<p>Supply the new name which can contain (A-Za-z.~)</p>
						<input placeholder="Rename to" id="new_name" class="form-control" />
						<br/>
						<input type="submit" class="btn btn-danger w-100" name="action" value="DONE" onclick="var val = $('#new_name').val(); if( val ){ location.href = window.currentIRL+'&name='+encodeURIComponent(val); }" />
					</div>
				</div>
			</div>
		</div>

		<div id="as" class="fixed-top alert alert-success text-center rounded-0"><?php echo $success; ?></div>
		<div id="ae" class="fixed-top alert alert-danger text-center rounded-0"><?php echo $error; ?></div>

		<script type="text/javascript">
			function launchModal( id ){
				$('#container').modal();
				$('#container .modal-body>*').addClass('d-none');
				$('#'+id).removeClass('d-none');
			}

			setTimeout(function(){
				$('.alert').html('');
			}, 2000);

			function startUpload(){
				$('#container').submit();
				$('#upload_loader').addClass('d-flex');
			}

			function endUpload( response ){
				$('#upload_loader').removeClass('d-flex');
				$('#container').modal('hide');
				if( response=='success' ){
					$('#as').html('file(s) uploaded successfuly!');
				} else {
					$('#ae').html(response);
				}

				setTimeout(function(){
					$('.alert').html('');
					//$('#container').cancel();
				}, 3000);
			}

			es = $('.folder .btn.d');
			es.prop( 'disabled', true );
			es.prop('onclick', null);
		</script>

		<?php if( !empty($TM) ){ ?>
			
		<script type="text/javascript">
			
			$('tr .btn:not( .t )').remove();
			$('.t').removeClass('d-none');

			$('tr a').prop( 'href', 'javascript:void(0)' );
			$('.folder:nth-child(2) a').prop( 'href', './' );

		</script>

		<?php } ?>

		<style type="text/css">
			.dropdown-item {
				font-size: .93rem;
			}

			#body .alert:empty {
				display: none;
			}
		</style>
	</body>
</html>