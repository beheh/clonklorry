$(document).ready(function() {
    $('#search-item').click(function(e) {
	e.preventDefault();
	$('#search-global').blur();
	$('#search-global').focus();
	$('.nav-collapse').collapse();
    }); 
    $('#search-global').typeahead({
	source:['Codename: Modern Combat', 'Hazard', 'OC']
    });
});