$(document).ready(function() {
	
	$.ajaxSetup({ cache: false });
	
	function renderEsObject(esObject, wrapper) {
		var url = esObject.attr("data-url");
		if(typeof wrapper == 'undefined')
			var wrapper = esObject.parent();
		$.get(url, function(data) {
			wrapper.html('').append(data).css({height: 'auto', width: 'auto'});
			if (data.toLowerCase().indexOf('data-view="lock"') >= 0)
				setTimeout(function(){ renderEsObject(esObject, wrapper);}, 1111);
		});
		esObject.removeAttr("data-type");
	}
	
	$("img[data-type='esObject']:near-viewport(400)").each(function() {
		renderEsObject($(this));
	})
	
	$(window).scroll(function() {
		$("img[data-type='esObject']:near-viewport(400)").each(function() {
			renderEsObject($(this));
		})
	});

})