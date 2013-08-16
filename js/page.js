$(document).ready(function() {
	width = 0;
	height = 0;
	getSizes();

	var hash = getHash();
	if(!hash[0]) {
		setHash('Start');
	}
	
	reloadPage();
	
	$(window).on('hashchange', function() {
		reloadPage();
	});
});

function getSizes() {
	width = $(window).width();
	height = $(window).height();
	viewport_width = Math.round(width*0.75);
	viewport_height = height;
}

function getStyle(style, callback) {
	$.getJSON('?q=style/' + style, function(data) {
		callback(data);
	});
}

function pageStart(args) {
	setHash('Search');
}

function pageSearch(args) {
	$('#main').html(
		'<div id="left" style="width: ' + Math.floor(width*0.25) + 'px; height: ' + height + 'px;"></div>' +
		'<div id="content" style="width: ' + Math.floor(width*0.75) + 'px; height: ' + height + 'px;"></div>'
	);
}

function pageMovie(args) {
	$('#main').html('');
}

function reloadPage() {
	var hash = getHash();
	var functionName = 'page' + hash[0];
	var fn = window[functionName];
	if(typeof fn === 'function') {
		fn(hash);
		$.getScript('js/' + hash[0].toLowerCase() + '.js', function(data, textStatus, jqxhr) {
			var functionName = 'load' + hash[0];
			var fn = window[functionName];
			if(typeof fn === 'function') {
				fn(hash);
			}
		});
	}
}

function getHash() {
	var hash = window.location.hash;
	hash = hash.replace('#', '');
	var parts = hash.split('|');
	return parts;
}

function setHash(hash) {
  if(hash != undefined) {
    var strBrowser = navigator.userAgent.toLowerCase();

    if (strBrowser.indexOf('chrome') > 0 || strBrowser.indexOf('safari') > 0) {
        this.location.href = "#" + hash;
        reloadPage();
    }
    else {
        this.window.location.hash = "#" + hash;
        reloadPage();
    }
  }	
}

function render(renderskeleton, renderdata) {
	var page = renderskeleton.page;
	for (var key in renderdata) {
		var newkey = new RegExp('%' + key + '%', "g");

		page = page.replace(newkey, renderdata[key]);
	}

	return page;
}