$(function () {
	$('#inputtags').attr('autocomplete', 'off');
	$('#inputtags').tagSuggest({
		url: 'tags.php',
		delay: 250,
		separator: ', ',
		tagContainer: 'p',
	});
	
	var uploader = new plupload.Uploader({
		runtimes : 'gears,html5,flash,silverlight,browserplus',
		browse_button : 'addimages',
		container : 'imageslist',
		max_file_size : '10mb',
		url : 'upload.php',
		flash_swf_url : 'js/plupload.flash.swf',
		silverlight_xap_url : 'js/plupload.silverlight.xap',
		multipart : true,
		filters : [
		           {title : "Image files", extensions : "jpg,gif,png,bmp"}
		           ]
		});
		
		uploader.bind('Init', function(up, params) {
			//$('#filelist').html("<div>Current runtime: " + params.runtime + "</div>");
		});
		
		$('#inputsubmit').click(function(e) {
			uploader.settings['multipart_params'] = {
				'tags': $('#inputtags').attr('value'),
				'submit': 'Upload'
			};
			uploader.start();
			e.preventDefault();
		});
		
		uploader.init();
		
		uploader.bind('FilesAdded', function(up, files) {
			$.each(files, function(i, file) {
				$('#imageslist').append(
					'<div id="' + file.id + '">' +
					file.name + ' (' + plupload.formatSize(file.size) + ') <b></b>' +
					'</div>');
			});
		 
			up.refresh(); // Reposition Flash/Silverlight
		});
		
		uploader.bind('UploadProgress', function(up, file) {
			$('#' + file.id + " b").html(file.percent + "%");
		});
		
		uploader.bind('Error', function(up, err) {
			$('#imageslist').append("<div>Error: " + err.code +
				", Message: " + err.message +
				(err.file ? ", File: " + err.file.name : "") +
				"</div>");
		
			up.refresh(); // Reposition Flash/Silverlight
		});
		
		uploader.bind('FileUploaded', function(up, file) {
			$('#' + file.id + " b").html("100%");
		});
	});

	$('#inputimages').hide();
});
