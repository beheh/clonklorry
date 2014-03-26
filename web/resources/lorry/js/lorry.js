$(document).ready(function() {
    /*$('#search-item').click(function(e) {
	e.preventDefault();
	$('#search-global').blur();
	$('#search-global').focus();
	});
    $('#search-global').typeahead({
	source:['Codename: Modern Combat', 'Hazard', 'OC']
    });*/
});

var url = document.location.toString();
if (url.match('#')) {
    $('.nav-tabs a[href=#'+url.split('#')[1]+']').tab('show') ;
} 

// Change hash for page-reload
$('.nav-tabs a').on('shown', function (e) {
    window.location.hash = e.target.hash;
})