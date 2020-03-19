(function() {
	tinymce.create('tinymce.plugins.sd', {
	   init : function(ed, url) {
		  ed.addButton('recentposts', {
			 title : 'Recent posts',
			 image : url+'/icon-128.png',
			 onclick : function() {
				var posts = prompt("Number of posts", "1");
				var text = prompt("List Heading", "This is the heading text");
 
				if (text != null && text != ’){
				   if (posts != null && posts != ’)
					  ed.execCommand('mceInsertContent', false, '[recent-posts posts="'+posts+'"]'+text+'[/recent-posts]');
				   else
					  ed.execCommand('mceInsertContent', false, '[recent-posts]'+text+'[/recent-posts]');
				}
				else{
				   if (posts != null && posts != ’)
					  ed.execCommand('mceInsertContent', false, '[recent-posts posts="'+posts+'"]');
				   else
					  ed.execCommand('mceInsertContent', false, '[recent-posts]');
				}
			 }
		  });
	   },
	   createControl : function(n, cm) {
		  return null;
	   },
	   getInfo : function() {
		  return {
			 longname : "Recent Posts",
			 author : 'Adrii',
			 authorurl : 'https://github.com/AdrianVillamayor/',
			 infourl : '',
			 version : "1.0"
		  };
	   }
	});

	tinymce.PluginManager.add('sd', tinymce.plugins.sd);
 })();