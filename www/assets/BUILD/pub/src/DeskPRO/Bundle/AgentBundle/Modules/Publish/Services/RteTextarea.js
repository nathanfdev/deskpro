import $ from 'jquery';

require('froala-editor');
require('froala-editor/js/plugins/align.min');
require('froala-editor/js/plugins/code_beautifier.min');
require('froala-editor/js/plugins/code_view.min');
require('froala-editor/js/plugins/colors.min');
require('froala-editor/js/plugins/font_family.min');
require('froala-editor/js/plugins/font_size.min');
require('froala-editor/js/plugins/fullscreen.min');
require('froala-editor/js/plugins/image.min');
require('froala-editor/js/plugins/link.min');
require('froala-editor/js/plugins/lists.min');
require('froala-editor/js/plugins/paragraph_format.min');
require('froala-editor/js/plugins/table.min');
require('froala-editor/js/plugins/word_paste.min');

class RteTextarea {
  // eslint-disable-next-line class-methods-use-this
  init(field, options) {
    let localOptions = options || {};

    $.FroalaEditor.DefineIcon('dpMedia', { NAME: 'picture-o' });
    $.FroalaEditor.RegisterCommand('dpMedia', {
      title:                'Upload Image',
      focus:                false,
      undo:                 true,
      refreshAfterCallback: true,
      callback() {
        window.MEDIA_MANAGER_WINDOW.bindToEditor(this);
        window.MEDIA_MANAGER_WINDOW.open();
      }
    });

    const defaultOptions = {
      htmlAllowedAttrs: [
        'class', 'frameborder', 'height', 'id', 'longdesc', 'marginheight', 'marginwidth',
        'name', 'scrolling', 'src', 'style', 'title', 'width', 'webkitallowfullscreen',
        'mozallowfullscreen', 'allowfullscreen'
      ],
      toolbarButtons: [
        'bold', 'italic', 'underline', '|', 'align', 'color', '|', 'paragraphFormat', 'fontFamily', 'fontSize',
        'formatUL', 'formatOL', '|', 'indent', 'outdent', '|', 'insertLink', 'dp_media', 'insertTable', '|',
        'dpMedia', 'html', 'clearFormatting', 'fullscreen'],
      key:             'qENARBFSTb1G1QJg1RA==',
      paragraphFormat: {
        N:          'Paragraph',
        H2:         'Heading 1',
        H3:         'Heading 2',
        H4:         'Heading 3',
        H5:         'Heading 4',
        BLOCKQUOTE: 'Quote',
        CODE:       'Code Box'
      },
      imageUploadMethod: 'POST',
      imageUploadParams: { _rt: window.DP_REQUEST_TOKEN, json: true },
      imageUploadURL:    `${BASE_URL}agent/misc/accept-redactor-image-upload`, // eslint-disable-line no-undef
      imageDefaultWidth: 0
    };

    localOptions = Object.merge(defaultOptions, localOptions || {});

    const rte = $(field).froalaEditor(localOptions);
    $(field).on('froalaEditor.focus', () => {
      window.DeskPRO_Window.keyboardShortcuts.isPaused = true;
    });
    $(field).on('froalaEditor.blur', () => {
      window.DeskPRO_Window.keyboardShortcuts.isPaused = false;
    });

    return rte;
  }
}

export default RteTextarea;
