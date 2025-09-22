(function () {
	tinymce.PluginManager.add('opt_bud_editor_plugin', function (editor, url) {

		// Add Button to Visual Editor Toolbar
		editor.addButton('addbutton', {
			icon: 'button',
			text: '',
			tooltip: 'Insert Button',
			onclick: function () {
				editor.windowManager.open({
					title: 'Button Settings',
					body: [
						{ type: 'textbox', name: 'title', label: 'Title' },
						{ type: 'textbox', name: 'url', label: 'URL' }
					],
					onsubmit: function (e) {
						editor.insertContent(`<form><button type="submit" formaction="${e.data.url}" title="URL: ${e.data.url}">${e.data.title}</button></form>`)
					}
				})
			},
			onPostRender: function () {
				var _this = this;
				editor.on('NodeChange', function (e) {
					_this.active(editor.selection.getNode().closest('button') !== null);
				});
			}
		});

		var codes = [{ name: 'Page Title', value: '{{page_title}}' }, { name: 'Page URL', value: '{{page_url}}' }, { name: 'Recipe', value: '{{recipe}}' }];
		var items = [];
		tinymce.each(codes, function (item) {
			items.push({
				text: item.name,
				onclick: function () {
					editor.insertContent(item.value);
				}
			})
		});

		editor.addButton('custom_fields', {
			type: 'menubutton',
			icon: 'code',
			text: '',
			menu: items,
			tooltip: 'Custom Fields',
			onPostRender: function () {
				var _this = this;
				editor.on('NodeChange', function (e) {
					_this.active(/\{\{(page_title|page_url|recipe)\}\}/.test(e.element.innerHTML));
				});
			}
		});
	});
})();