$(function () {
	$('#inputtags').attr('autocomplete', 'off');
	$('#inputtags').tagSuggest({
		url: 'tags.php',
		delay: 250,
		separator: ', ',
		tagContainer: 'p',
	});
});

$(document).ready(function () {
	var uploading = false;	
	var filelist = new Array();
	
	/*
	$('#addimage').click(function() {
		uploading = true;
		$('#addimage').remove();
		$('#inputimagecontainer').append('<span class="text">&nbsp;</span><input type="file" size="39" name="image[]" />&nbsp;' +
			'<img src="images/add.png" id="addimage" alt="Add another image" title="Add another image" /><br /><br />');
		$('#addimage').click(arguments.callee);
	});
	*/
	
	function upload(event) {
		if (filelist.length == 0) return;

		var boundary = '------multipartformboundary' + (new Date).getTime();
		var dashdash = '--';
		var crlf     = '\r\n';

		/* Build RFC2388 string. */
		var builder = '';

		builder += dashdash;
		builder += boundary;
		builder += crlf;

		var xhr = new XMLHttpRequest();
		
		builder += 'Content-Disposition: form-data; name="submit"';
		builder += crlf;
		builder += 'Content-Type: text/plain';
		builder += crlf;
		builder += crlf;
		builder += 'Submit';
		builder += crlf;
		
		/* Write boundary. */
		builder += dashdash;
		builder += boundary;
		builder += crlf;

		/* For each dropped file. */
		for (var i = 0; i < filelist.length; i++) {
			var file = filelist[i];

			/* Generate headers. */			
			builder += 'Content-Disposition: form-data; name="image[]"';
			if (file.fileName) {
			  builder += '; filename="' + file.fileName + '"';
			}
			builder += crlf;

			builder += 'Content-Type: application/octet-stream';
			builder += crlf;
			builder += crlf; 

			/* Append binary data. */
			builder += file.getAsBinary();
			builder += crlf;

			/* Write boundary. */
			builder += dashdash;
			builder += boundary;
			builder += crlf;
		}
		
		/* Mark end of the request. */
		builder += dashdash;
		builder += boundary;
		builder += dashdash;
		builder += crlf;

		xhr.open("POST", "upload.php?response=json", true);
		xhr.setRequestHeader('content-type', 'multipart/form-data; boundary=' + boundary);
		xhr.sendAsBinary(builder);		
		
		xhr.onload = function(event) { 
			if (xhr.responseText) {
				var response = $.parseJSON(xhr.responseText);
				if (response.error != '') {
					alert('Upload error: ' + response.error);
					$('body').css({'overflow': 'visible'});
					$('#loading').css({'display': 'none'});
					$('#hide').remove();
				} else {
					window.location = response.url;
				}
			} else {
				alert('Upload error.');
				$('body').css({'overflow': 'visible'});
				$('#loading').css({'display': 'none'});
				$('#hide').remove();				
			}
		};
	}
	
	$('#submit').click(function(e) {
		e.preventDefault();
		
		$('body').append('<div id="hide" />');
				
		$('body').css({'overflow':'hidden'});
		
		$('#hide').css({
		 'background-color': '#000000',
		 'position': 'absolute',
		 'top': 0,
		 'left': 0,
		 'opacity': 0.8,
		 'width': $(document).width(),
		 'height': $(document).height(),
		 'z-index': 100	 
		});
		
		$('#loading').css({'display': 'block'});
		
		upload();
	});
	
	function setPopup(e) {		
		$('#imagePopup').empty().append('<img alt="' + $(this).text() + '" />');
		
		var reader = new FileReader();
		reader.onloadend = function() {
			$('#imagePopup img').attr('src', reader.result);
			$('#imagePopup').show();
		}
		reader.readAsDataURL(filelist[$(this).data('image')]);
		
		updatePopupPosition(e)
	}
	
	function updatePopupPosition(e) {
		
		var windowSize = getWindowSize();
		var popupSize = getPopupSize();
		
		if (windowSize.width + windowSize.scrollLeft < e.pageX + popupSize.width + 15) {
			$('#imagePopup').css("left", e.pageX - popupSize.width - 15);
		} else {
			$('#imagePopup').css("left", e.pageX + 15);
		}
		if (windowSize.height + windowSize.scrollTop < e.pageY + popupSize.height + 15) {
			$('#imagePopup').css("top", e.pageY - popupSize.height - 15);
		} else {
			$('#imagePopup').css("top", e.pageY + 15);
		}
	}
	
	function hidePopup(event)
	{
		$('#imagePopup').empty().hide();
	}

	function getWindowSize() {
		return {
			scrollLeft: $(window).scrollLeft(),
			scrollTop: $(window).scrollTop(),
			width: $(window).width(),
			height: $(window).height()
		};
	}
		
	function getPopupSize() {
		return {
			width: $('#imagePopup').width(),
			height: $('#imagePopup').height()
		};
	}
	
	function refreshFileList() {
		$('#imageslist').empty();
		if (filelist.length > 0) {
			$('#imageslist').show();
			for (var i = 0; i < filelist.length; i++) {
				$('#imageslist').append('<div class="imageslistentry">' + filelist[i].name + '</div>');
				$('.imageslistentry:last').data('image', i).hover(setPopup, hidePopup).mousemove(updatePopupPosition);
			}
		} else {
			$('#imageslist').hide();
		}
	}	
	
	$('#inputimages').change(function() {
		var files = document.getElementById('inputimages').files;
		for (var i = 0; i < files.length; i++) {
			var file = files[i];
			if (file.type.match(/image.*/)) {
				filelist[filelist.length] = file;
			}
		}
		refreshFileList();
	});
	
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
		
	dropbox.addEventListener('drop', function (e) {
		e.stopPropagation();
		e.preventDefault();
			
		var files = e.dataTransfer.files;
		
		for (var i = 0; i < files.length; i++) {
			var file = files[i];
			if (file.type.match(/image.*/)) {
				filelist[filelist.length] = file;
			}
		}
		refreshFileList();
	}, false);	
});
