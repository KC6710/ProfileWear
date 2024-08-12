CKEDITOR.plugins.add('elfinder', {
	init: function(editor) {
		editor.addCommand('elFinder', {
			exec: function(editor) {
				$('<div id="dc-elfinder-dialog" />').dialogelfinder({
					url : $('[data-elfinder-url]').data('elfinder-url'),
					width: '80%',
					height: '600px',
					onlyMimes: ['image'],
					getFileCallback: function(file) {
						var file = file[0] ? file[0] : file;

						CKEDITOR.instances[editor.name].insertHtml('<img src="' + file['url'] + '" alt="" title=""/>');

						$('#dc-elfinder-dialog').remove();
					}
				});
			}
		});

		editor.ui.addButton('elFinder', {
			label: 'elFinder',
			command: 'elFinder',
			icon: this.path + '/icon.png'
		});
	}
});
