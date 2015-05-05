/* List posts containing the attachment */

jQuery(function($){
	var loading = true;
	var postid = media_load_related.postid;
	console.log(postid);
	var content = $("#list-attachment-posts-meta .inside");
	var load_related_posts = function(){
		$.ajax({
			type       : "GET",
			data       : {
				action: "media_load_related_posts", 
				"postid": postid
			},
			dataType   : "html",
			url        : media_load_related.ajaxurl,
			beforeSend : function(){
				content.html('<img id="temp_load" src="' + media_load_related.pluginurl + '/img/loading-spinner.gif" style="display: block; margin: 10px auto;" />');
			},
			success    : function(data){
	            if ( data.length ){
	                $(data).hide();
	                content.append(data);
	                $("#temp_load").remove();
	                loading = false;
	                $(data).fadeIn(500);
	            } else {
	                content.html('<p>No related posts found.</p>');
	            }
			},
			error     : function(jqXHR, textStatus, errorThrown) {
				$("#temp_load").remove();
				alert(jqXHR + " :: " + textStatus + " :: " + errorThrown);
			}
		});
	}
	
	load_related_posts();
});