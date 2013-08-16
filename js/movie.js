$(document).ready(function() {
	loadMovie();
});

function loadMovie(hash) {
	getMovie(hash, function(data) {
		getStyle('divxplayer', function(renderskeleton) {
			html = '';
			var renderdata = new Object;
			renderdata['id'] = data._id;
			renderdata['type'] = data._source.videotype;
			renderdata['width'] = Math.round(width*0.45);
			renderdata['height'] = Math.round(width*0.2);
			html = html + render(renderskeleton, renderdata);
			$('#main').html(html);
			var bgimg = '?q=image/' + data._id + '/image/' + width + 'x' + height;
			$('body').css('background', 'url(' + bgimg + ')');
			loadControlsDivx();
		});
	});	
}

function loadControlsDivx() {
	var plugin;   
    if(navigator.userAgent.indexOf('MSIE')   != -1) {   
        plugin = document.getElementById('ie_plugin');   
    } else {   
        plugin = document.getElementById('np_plugin');   
    } 
    
    plugin.Play();
	$('#main').on('click', '#fullscreen', function() {
		plugin.GoFullscreen();
	})
	
	$('#main').on('click', '#play', function() {
		plugin.Play();
	})
	
	$('#main').on('click', '#pause', function() {
		plugin.Pause();
	})		

	$(document).keypress(function(e) {
        if (e.which == 32) 
        {
            plugin.Pause();
        };
    });
}

function getMovie(hash, callback) {
	$.getJSON('?q=getMovie/' + hash[1], function(data) {
		callback(data);
	});
}

