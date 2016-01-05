var redirect = window.location.href;
var uploading = false;

var uploader = new plupload.Uploader({
	browse_button: 'pickfiles',
	url: 'upload/',
	container: 'uploadform',
	drop_element: 'dropbox',
	multipart: true,
	//html5,flash,silverlight,html4
	runtimes: 'html5,html4', 
	
	init: {
		PostInit: function() {
			$('#upoad').click(function() {
				uploading = true;
				
				$('body').append('<div id="hide" />');
				
				$('#pickfiles').prop('disabled', true);
				$('#upoad').prop('disabled', true);
				$('#inputtags').prop('disabled', true);
								
				uploader.settings['multipart_params'] = {
					'tags': $('#inputtags').val(),
					'uploadid': $('#uploadform').data('uploadid'),
					'csrf_token': $('#uploadform').data('csrf'),
					'submit': 'Upload'
				};
				uploader.start();
			});
		},
		
		FilesAdded: function(up, files) {
			$('#imageslist').show();
			
			$.each(files, function(i, file) {
				$('#imageslist').append(
					'<div id="' + file.id + '">' +
					file.name + ' (' + plupload.formatSize(file.size) + ')' +
					'</div>');
			});
		 
			up.refresh(); // Reposition Flash/Silverlight
		},
		
		UploadFile: function(up, file) {
			$('#' + file.id).after('<div id="' + file.id + 'progress" />');
			$('#' + file.id + 'progress').addClass('progress').append('<div class="progress-bar"' +
					'role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" />');
		},
		
		UploadProgress: function(up, file) {
			$('#' + file.id + 'progress .progress-bar').css('width', file.percent + '%').html(file.percent + ' %');
		},
		
		Error: function(up, err) {
			$('#' + err.file.id + 'progress').remove();
			$('#' + err.file.id + "").html(err.file.name + ' <span class="glyphicon glyphicon-remove-sign upload-error" aria-hidden="true"></span>');
			
			up.refresh(); // Reposition Flash/Silverlight */
		},
		
		FileUploaded: function(up, file, response) {
			$('#' + file.id + 'progress').remove();
			
			var obj = JSON.parse(response.response); // parse JSON response
			// check for server-side error
			if (typeof(obj.status) != 'undefined' && obj.status == 'ok') {
				redirect = obj.redirect;
				$('#' + file.id).html(file.name + ' <span class="glyphicon glyphicon-ok-sign upload-success" aria-hidden="true"></span>');
			} else {
				up.trigger("Error", {
					message: obj.error.message,
					code: obj.error.code,
					file: file});
				return false;
			}
		},
		
		UploadComplete: function(up, files) {
			window.location = redirect;
		}
	}
});

uploader.init();

window.addEventListener('dragenter', function (e) {
	if (uploading) return;
	
	e.stopPropagation();
	e.preventDefault();
	
	$('#dropbox').fadeIn();
		
}, false);

var dropbox = document.getElementById('dropbox');			
var dropboxHideTimer;

dropbox.addEventListener('dragover', function(e) {
	e.stopPropagation();
	e.preventDefault();
	
	clearTimeout(dropboxHideTimer);
	dropboxHideTimer = setTimeout("$('#dropbox').fadeOut();", 250);
}, false);
