//===================================================================================================================================================
// DEVELOPMENT SHOW

function verifyLogArea() {
	if ($('#log').length == 0) {
		$('body')
				.prepend(
						'<div id="log" style="position:absolute; opacity:0.5; background:#ffa; padding:10px 20px; margin:10px 20px; z-index:1;"></div>');
	}
}

function log(msg, separator) {
	verifyLogArea();
	$('#log').prepend(msg + (separator == null ? '<br />' : separator));
}

// ================================================================================================================================================
// PRODUCTION SHOW

function setMsg(type, msg) {

	if (type == 'error') {
		var process_result = $('.process-results .btn-danger');
	} else if (type == 'warning') {
		var process_result = $('.process-results .btn-warning');
	} else if (type == 'success') {
		var process_result = $('.process-results .btn-success');
	} else {
		alert('Tipo de mensagem desconhecida (' + type + ').');
		return false;
	}
	process_result.html(msg);
	process_result.show();	
	setTimeout(function() {
		process_result.hide();
	}, 120000);

}

function setError(msg) {	
	setMsg('error', msg);
}
function setWarning(msg) {
	setMsg('warning', msg);
}
function setSuccess(msg) {
	setMsg('success', msg);
}

// ================================================================================================================================================
// DOCUMENT READY

$(document).ready(function() {
	
});