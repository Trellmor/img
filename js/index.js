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
	$('#addimage').click(function() {
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
		$('body').append('<div id="dropbox" />');
		$('#dropbox').append('<h1>Drop files here</h1>');
	}, true);
	
	window.addEventListener('dragleave', function(e) {
		$('#dropbox').remove();
	}, true);
});
