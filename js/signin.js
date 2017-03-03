function onGooglePlatformSignIn(googleUser) {
	var id_token = googleUser.getAuthResponse().id_token;
	var url = $('#g-signin-button').data('loginpage');
	$.post(url, { 'id_token': id_token }, function(data) {
		if (typeof(data.status) != 'undefined' && data.status == 'ok') {
			var url = $('#site-navigation').data('reload');
			$.get(url, function(data) {
				$('#site-navigation').replaceWith(data);
			});
		} else {
			signOut();
		}
	});
}


function onGooglePlatformSignInFailure(error) {
	console.log(error);
}

function onGooglePlatformLoaded() {
	gapi.load('auth2', function() {
		var auth2 = gapi.auth2.init();
		gapi.signin2.render('g-signin-button', {
			'scope': 'email',
			'onsuccess': onGooglePlatformSignIn,
			'onfailure': onGooglePlatformSignInFailure
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