$(document).ready(function(){

var usersSource = new Bloodhound({
	datumTokenizer: function(d) { return Bloodhound.tokenizers.whitespace(d.fbid); },
	queryTokenizer: Bloodhound.tokenizers.whitespace,
	prefetch: '/ajax-users.php',
	remote: '/ajax-users.php?query=%QUERY'
});

usersSource.initialize();

$('.js-switch').each(function(index, element) {
	new Switchery(element, { color: '#64BD63', secondaryColor: '#FEC200' });
});

$('.js-switch').change(function() {
	var is_checked = $(this).is(':checked');
	var checked_string = $(this).is(':checked') ? '1' : '0';
	var current_id = $(this).attr('id');
	var label_id = '#label_'+current_id;
	var label_text = $(this).is(':checked') ? 'Sim' : 'N&atilde;o';
	$(label_id).html('<img src="/images/l.gif" alt="Carregando" width="16" height="16" />');
	var xhr_data = {
		type: "POST",
		url: "/ajax-attend.php",
		data: {
			eventfbid: $('#'+current_id).data('eventfbid'),
			userfbid: $('#'+current_id).data('userfbid'),
			actually_attended: checked_string,
			benefit: $('#'+current_id).data('benefitid'),
		},
		cache: false,
	};
	$.ajax(xhr_data)
		.done(function(msg) {
			$(label_id).text(label_text);
			$('#'+current_id).data('checked-by-client', is_checked);
		}).fail(function(msg) {
			$(label_id).text('Erro');
			$('#'+current_id).data('has-error', true);
			$('#'+current_id).addClass('has-error');
		}).always(function(msg) {
			console.log(msg);
		});
});

//var clickCheckbox = document.querySelector('.js-check-click');
//var clickButton = document.querySelector('.js-check-click-button');
/*
$('.switchery small').mousedown(function() {
	isDragging = true;
	$(window).unbind("mousemove");
}).mouseup(function() {
	var wasDragging = isDragging;
    isDragging = false;
    $(window).unbind("mousemove");
    if (!wasDragging) { //was clicking
        console.log($(this).parent('.switchery'));
    }
});
*/

});
