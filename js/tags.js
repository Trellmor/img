if (typeof $.fn.select2 !== 'undefined') {
	$("#inputtags").select2({
		// enable tagging
		tags: true,
		tokenSeparators: [','],
		ajax: {
			delay: 250,
			processResults: function (data, page) {
				return {
					results: data
				};
			}
		}
	});
}