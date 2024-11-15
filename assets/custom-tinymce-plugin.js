(function() {
    tinymce.PluginManager.add('custom_tinymce_plugin', function(editor, url) {
        editor.addButton('custom_button', {
            type: 'listbox',
            text: 'Select wildcards',
            icon: false,
            onselect: function(e) {
                var value = this.value();
                // Do something with the selected value
                // console.log('Selected value:', value);
                editor.insertContent(value);
            },
            values: [
                { text: 'User name', value: '{{user_name}}' },
                { text: 'Item name', value: '{{item_name}}' },
                { text: 'Item image', value: '{{item_image}}' },
                // { text: 'Item link', value: '{{item_link}}' }
            ]
        });
    });
})();
