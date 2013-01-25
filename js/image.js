$.extend({
	getUrlVars: function(){
		var vars = [], hash;
		var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
		for (var i = 0; i < hashes.length; i++) {
			hash = hashes[i].split('=');
			vars.push(hash[0]);
			vars[hash[0]] = hash[1];
		}
		return vars;
	},
	getUrlVar: function(name){
		return $.getUrlVars()[name];
	}
});

$(document).ready(function() {	
	$('#imagename').hover(function() {
		$('#imagename').append('<a id="imagedelete" href="action.php?action=delete&amp;type=image&amp;image=' + $.getUrlVar('i') + '">' +
		'<img src="images/delete.png" alt="Delete" /></a>');
	}, function() {
		$('#imagedelete').empty().remove();
	});
	
	$('#tags').hover(function() {
		$('#tags').append(' <img id="tagsedit" src="images/edit.png" alt="Edit" />');
		$('#tagsedit').css('cursor', 'pointer').click(function(e) {
			$('#tags').unbind();
			var tags = '';
			$('#tags a').each(function() { tags += $(this).text() + ', '; });
			$('#tags').empty().css('text-align', 'left').append('<form action="action.php?action=edit&amp;type=tags&amp;image=' + $.getUrlVar('i') + '" method="post">' + 
				'<span class="text">Tags:</span><input id="inputtags" type="text" name="tags" value="' + tags + '" size="50" /> ' + 
				'<span class="text">&nbsp;</span><input type="submit" value="Save" />' +
				'<span class="tagMatches"></span>' + 
				'</form>');
			$('#inputtags').attr('autocomplete', 'off');
			$('#inputtags').tagSuggest({
				url: 'tags.php',
				delay: 250,
				separator: ', ',
				tagContainer: 'p',
			});
		});
	}, function() {
		$('#tagsedit').empty().remove();
	}); 
	/*
	$('#imagename').mouseover(function() {
		$('#imagename h2').append('<a id="imagedelete" href="actions.php?action=delete&amp;type=image&amp;image=' + $.getUrlVar('i') + '">' +
		'<img src="images/delete.png" alt="Delete" /></a>');
	});
	$('#imagename').mouseout(function() {
		$('#imagedelete').empty().remove();
	});
	*/
});
