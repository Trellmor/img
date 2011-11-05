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
	
	var boundary;
	var dashdash = '--';
	var crlf     = '\r\n';
	
	function utf16to8(str) {
	    var out, i, len, c;

	    out = "";
	    len = str.length;
	    for(i = 0; i < len; i++) {
		c = str.charCodeAt(i);
		if ((c >= 0x0001) && (c <= 0x007F)) {
		    out += str.charAt(i);
		} else if (c > 0x07FF) {
		    out += String.fromCharCode(0xE0 | ((c >> 12) & 0x0F));
		    out += String.fromCharCode(0x80 | ((c >>  6) & 0x3F));
		    out += String.fromCharCode(0x80 | ((c >>  0) & 0x3F));
		} else {
		    out += String.fromCharCode(0xC0 | ((c >>  6) & 0x1F));
		    out += String.fromCharCode(0x80 | ((c >>  0) & 0x3F));
		}
	    }
	    return out;
	}
	
	var tryFFSend = false;
	
	if (typeof BlobBuilder === 'undefined') {
		tryFFSend = true;
		
		function BlobBuilder () {
			var string = '';
			this.append = function(s) {
				string += s;
			}
			
			this.getBlob = function() {
				return string;
			}				
		}
	}
	
	function upload() {
		if (filelist.length == 0) return;

		boundary = '------multipartformboundary' + (new Date).getTime();

		/* Build RFC2388 string. */
		var builder = new BlobBuilder();

		builder.append(dashdash);
		builder.append(boundary);
		builder.append(crlf);
		
		//Submit button
		builder.append('Content-Disposition: form-data; name="submit"');
		builder.append(crlf);
		builder.append('Content-Type: text/plain');
		builder.append(crlf);
		builder.append(crlf);
		builder.append('Submit');
		builder.append(crlf);
		/* Write boundary. */
		builder.append(dashdash);
		builder.append(boundary);
		builder.append(crlf);
		
		
		//tags
		builder.append('Content-Disposition: form-data; name="tags"');
		builder.append(crlf);
		builder.append('content-type: text/plain;charset=UTF-8');
		builder.append(crlf);
		builder.append(crlf);
		builder.append(utf16to8($('#inputtags').attr('value')));	
		builder.append(crlf);
		/* Write boundary. */
		builder.append(dashdash);
		builder.append(boundary);
		builder.append(crlf);
		
		addFileToBuilder(0, builder);
	}
	
	function addFileToBuilder(currFile, builder) {
		var file = filelist[currFile];
		currFile++;

		/* Generate headers. */			
		builder.append('Content-Disposition: form-data; name="image[]"');
		if (file.name) {
		  builder.append('; filename="' + file.name + '"');
		}
		builder.append(crlf);

		builder.append('Content-Type: application/octet-stream');
		builder.append(crlf);
		builder.append(crlf); 

		/* Append binary data. */
		var reader = new FileReader();
		reader.onloadend = function() {
			builder.append(reader.result);
			builder.append(crlf);

			/* Write boundary. */
			builder.append(dashdash);
			builder.append(boundary);
			builder.append(crlf);
			
			if (currFile < filelist.length) {
				addFileToBuilder(currFile, builder);
			} else {
				sendRequest(builder);
			}
		}
		reader.readAsBinaryString(file);
	}
		
	function sendRequest(builder) {
		/* Mark end of the request. */
		builder.append(dashdash);
		builder.append(boundary);
		builder.append(dashdash);
		builder.append(crlf);
		
		
		var xhr = new XMLHttpRequest();
		xhr.open("POST", "upload.php?response=json", true);
		xhr.setRequestHeader('content-type', 'multipart/form-data; boundary=' + boundary);
		if (tryFFSend) {
			xhr.sendAsBinary(builder.getBlob())
		} else {
			xhr.send(builder);
		}
		
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
		if (typeof FileReader === 'undefined') return;
		
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
		if (typeof FileReader === 'undefined') return;
		var files = document.getElementById('inputimages').files;
		for (var i = 0; i < files.length; i++) {
			var file = files[i];
			if (file.type.match(/image.*/)) {
				filelist[filelist.length] = file;
			}
		}
		refreshFileList();
	});
	
	//If filereader doesn't exist, Drag'n'Drop support isn't working
	if (typeof FileReader !== 'undefined') {
	
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
	}
});
