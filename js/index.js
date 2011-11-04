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
	
	$('#addimage').click(function() {
		uploading = true;
		$('#addimage').remove();
		$('#inputimagecontainer').append('<span class="text">&nbsp;</span><input type="file" size="39" name="image[]" />&nbsp;' +
			'<img src="images/add.png" id="addimage" alt="Add another image" title="Add another image" /><br /><br />');
		$('#addimage').click(arguments.callee);
	});
	
	$('#submit').click(function() {		
		$('body').append('<div id="hide" />');
				
		$('body').css({'overflow':'hidden'});
		
		$('#hide').css({
		 'background-color': '#000000',
		 'position': 'absolute',
		 'top': 0,
		 'left': 0,
		 'opacity': 0.8,
		 'width':$(document).width(),
		 'height':$(document).height()
		});
		
		$('#loading').css({'display': 'block'});
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
				alert(file.name);
			}
		}
	}, false);	
});
