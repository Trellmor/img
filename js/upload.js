if (typeof plupload !== 'undefined') {

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
			$('#image-list').show();
			
			$.each(files, function(i, file) {
				$('#image-list').append('<div id="' + file.id + '">');
				var div = $('#' + file.id);

				div.append(file.name + ' (' + plupload.formatSize(file.size) + ') ');
				div.append('<a tabindex="0" role="button" class="img-popover" data-toggle="popover" data-file="' + file.id + '">' + 
						'<span class="glyphicon glyphicon-picture" aria-hidden="true"></span>' + 
						'</a> ');
				div.append('<a tabindex="0" role="button" class="img-remove" data-file="' + file.id + '">' +
						'<span class="glyphicon glyphicon-minus-sign" aria-hidden="true"></span>' +
						'</a>');
			});
		 
			up.refresh(); // Reposition Flash/Silverlight
		},
		
		FilesRemoved: function(up, files) {
			$.each(files, function(i, file) {
				$('#' + file.id).remove();
			});
		},

		UploadFile: function(up, file) {
			var div = $('#' + file.id);
			div.html(file.name + ' <span class="glyphicon glyphicon-upload" aria-hidden="true"></span>');
			scrollTo(div)
			div.after('<div id="' + file.id + 'progress" />');
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

function scrollTo(element) {
	$('html, body').animate({
		scrollTop: element.offset().top
	}, 2000);
}

function imgpopover_getTitle() {
	var file = uploader.getFile($(this).data('file'));
	return file.name;
}

function imgpopover_getContent() {
	var file = uploader.getFile($(this).data('file'));
	var source = file.getSource();
	var img = new moxie.image.Image();
	img.onload = function() {
		$('#img' + file.id).attr('src', this.getAsDataURL());
	}
	img.load(source);
	return '<img id="img' + file.id + '" class="img-responsive" />';
}

$('#image-list').delegate('.img-remove', 'click', function () {
	uploader.removeFile($(this).data('file'));
});

$('body').popover({
	html: true,
	trigger: 'hover focus',
	selector: '.img-popover',
	template: '<div class="popover" role="tooltip"><h3 class="popover-title"></h3><div class="popover-content"></div></div>',
	title: imgpopover_getTitle,
	content: imgpopover_getContent,
});

//=== Clipboard ================================================================

document.onkeydown = function(e) { return on_keyboard_action(e); }
document.onkeyup = function(e) { return on_keyboardup_action(e); }

var ctrl_pressed = false;

function on_keyboard_action(event){
	k = event.keyCode;
	//ctrl
	if (k==17) {
		if(ctrl_pressed == false)
			ctrl_pressed = true;
		if (!window.Clipboard)
			pasteCatcher.focus();
	}
}
function on_keyboardup_action(event) {
	k = event.keyCode;
	//ctrl
	if(k==17)
		ctrl_pressed = false;
}

//firefox
var pasteCatcher;
if (!window.Clipboard) {
	pasteCatcher = document.createElement("div");
	pasteCatcher.setAttribute("id", "paste_ff");
	pasteCatcher.setAttribute("contenteditable", "");
	pasteCatcher.style.cssText = 'opacity:0;position:fixed;top:0px;left:0px;';
	pasteCatcher.style.marginLeft = "-20px";
	document.body.appendChild(pasteCatcher);
	pasteCatcher.focus();

	document.getElementById('paste_ff').addEventListener('DOMSubtreeModified', function() {
		if(pasteCatcher.children.length == 1){
			img = pasteCatcher.firstElementChild.src;

			var blob = dataURLtoBlob(img);

			paste_createImage(blob);
			pasteCatcher.innerHTML = '';
		}
	}, false);
}

function dataURLtoBlob(dataurl) {
    var arr = dataurl.split(',');
    var mime = arr[0].match(/:(.*?);/)[1];
    var bstr = atob(arr[1]);
    var n = bstr.length
    var u8arr = new Uint8Array(n);
    while(n--){
        u8arr[n] = bstr.charCodeAt(n);
    }
    return new Blob([u8arr], {type:mime});
}

function pasteHandler(e) {
	if(e.clipboardData) {
		var items = e.clipboardData.items;
		if (items) {
			for (var i = 0; i < items.length; i++) {
				if (items[i].type.indexOf("image") !== -1) {
					var blob = items[i].getAsFile();
					//var URLObj = window.URL || window.webkitURL;
					//var source = URLObj.createObjectURL(blob);
					paste_createImage(blob);
				}
			}
		}
	} else {
		setTimeout(paste_check_Input, 1);
	}
}

//chrome
window.addEventListener("paste", pasteHandler);

function paste_check_Input() {
	var child = pasteCatcher.childNodes[0];
	pasteCatcher.innerHTML = "";
	if (child) {
		if (cild.tagName === "IMG") {
			paste_createImage(child.src);
		}
	}
}

function pad(number) {
	return ('0' + number).slice(-2);
}

function getDateString() {
	var date = new Date();
	return date.getFullYear() + '-' + pad(date.getMonth() + 1) + '-' + pad(date.getDate()) + 
		'_' + pad(date.getHours()) + pad(date.getMinutes()) + pad(date.getSeconds());
}

function getExtension(blob) {
	switch(blob.type) {
	case 'image/gif':
		return '.gif';
	case 'image/jpeg':
		return '.jpg'
	case 'image/png':
		return '.png';
	case 'image/bmp':
		return '.bmp';
	}
}

function paste_createImage(blob) {
	//Add do plupload
	var image = new moxie.image.Image();
	var file = new moxie.file.File(null, blob);
	file.name = 'clipboard-' + getDateString() + getExtension(blob);
	uploader.addFile(file);
}

//=== /Clipboard ===============================================================
}