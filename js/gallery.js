$('#blueimp-gallery')
	.on('open', function (event) {
		$('meta[name=viewport]').attr('content', 'width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, user-scalable=no');
	})
	.on('closed', function (event) {
		$('meta[name=viewport]').attr('content', 'width=device-width, initial-scale=1');
	});