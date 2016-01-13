function onGooglePlatformSignIn(googleUser) {
	var id_token = googleUser.getAuthResponse().id_token;
	var url = $('#g-signin-button').data('loginpage');
	$.post(url, { 'id_token': id_token }, function(data) {
		if (typeof(data.status) != 'undefined' && data.status == 'ok') {
			$('#g-signin-button').addClass('hidden');
			$('#g-signout-button').removeClass('hidden');
		} else {
			signOut();
		}
	});
}

function onGooglePlatformLoaded() {
	gapi.load('auth2', function() {
		var auth2 = gapi.auth2.init();
		gapi.signin2.render('g-signin-button', {
			'scope': 'email',
			'onsuccess': onGooglePlatformSignIn
		});
	});
}

function serverSignOut() {
	var url = $('#g-signout-button').data('logoutpage');
	$.get(url, function(data) {
		location.reload(true);
	});
}

function signOut() {
	var auth2 = gapi.auth2.getAuthInstance();
	auth2.signOut().then(serverSignOut);
}

$('#g-signout-button').click(function() {
	signOut();
});