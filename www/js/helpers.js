if (typeof location.origin === 'undefined')
location.origin = location.protocol + '//' + location.host;

function confirmOpen(message, pageOnServer) {
	if (confirm(message))
		window.location.href = location.origin+'/'+pageOnServer;
}
