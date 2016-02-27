if (typeof blueimp !== 'undefined') {

$('#blueimp-gallery')
	.on('open', function (event) {
		$('meta[name=viewport]').attr('content', 'width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, user-scalable=no');
	})
	.on('closed', function (event) {
		$('meta[name=viewport]').attr('content', 'width=device-width, initial-scale=1');
	})
	.on('slide', function (event, index, slide) {
		var gallery = $('#blueimp-gallery').data('gallery');
		var item = $(gallery.list[index]);

		$.bbq.pushState({'viewer': item.data('id')});
	})
	.on('close', function (event, index) {
		$.bbq.removeState('viewer');
	});

$(window).hashchange(function() {
	var gallery = $('#blueimp-gallery').data('gallery');
	if (typeof gallery === 'undefined') return;

	var id = $.bbq.getState('viewer');
	if (typeof id === 'undefined') {
		gallery.close();
		return;
	}

	for (var i = 0; i < gallery.list.length; i++) {
		var item = $(gallery.list[i]);
		if (item.data('id') == id) {
			if (gallery.getIndex() != i) {
				gallery.slide(i);
			}
			break;
		}
	}
});

$(document).ready(function () {
	var id = $.bbq.getState('viewer');
	if (typeof id !== 'undefined') {
		$('a[data-id="' + id + '"').click()
	}
});
}

