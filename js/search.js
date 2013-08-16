$(document).ready(function() {
	searchresult = null;
});

function loadSearch(hash) {
	search(function(data) {
		leftMenu();
		renderSearch(data);
	});	
}

function leftMenu() {
	$('#left').html('test');
}

function search(callback) {
	var amount = Math.round(4*(viewport_height/viewport_width))*10;

	$.getJSON('?q=search/' + amount, function(data) {
		callback(data);
	});
}

function renderSearch(search_results)
{
	var icon_width = Math.round((viewport_width/10)-12);
	getStyle('movie', function(renderskeleton) {
		html = '';
		for (var i=0;i<search_results.hits.length;i++) {
			var renderdata = new Object;
			var source = search_results.hits[i]._source;
			renderdata['id'] = search_results.hits[i]._id;
			renderdata['img'] = '?q=image/' + renderdata['id'] + '/poster/' + icon_width + 'x' + icon_width*1.5;
			html = html + render(renderskeleton, renderdata);
		}
		$('#content').html(html);	
	})
	
}
