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
		$('#tags').append('<a id="tagsedit"><img src="images/edit.phn" alt="Edit" /></a>').click(funcion(e) {
			alert('test')
		});
	}, function() {
		$('#tagsedit').emtpy().rempoe()
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