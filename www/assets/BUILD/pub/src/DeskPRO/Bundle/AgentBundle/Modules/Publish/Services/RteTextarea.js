import $ from 'jquery';

require('froala-editor/js/froala_editor.pkgd.min');

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
    $.FroalaEditor.DefineIcon('imageVAlign', { NAME: 'arrows-v' });
    $.FroalaEditor.RegisterCommand('imageVAlign', {
      title:                'Vertical Align',
      focus:                false,
      undo:                 false,
      refreshAfterCallback: false,
      type:                 'dropdown',
      options:              {
        top:    'Top',
        middle: 'Middle',
        bottom: 'Bottom',
      },
      callback(cmd, val) {
        this.image.applyStyle(`${val}Aligned`);
      }
    });

    const defaultOptions = {
      htmlAllowedAttrs: [
        'class', 'frameborder', 'height', 'id', 'longdesc', 'marginheight', 'marginwidth',
        'name', 'scrolling', 'src', 'style', 'title', 'width', 'webkitallowfullscreen',
        'mozallowfullscreen', 'allowfullscreen', 'href', 'colspan', 'rowspan'
      ],
      toolbarButtons: [
        'bold', 'italic', 'underline', '|', 'align', 'color', '|', 'paragraphFormat', 'fontFamily', 'fontSize',
        'formatUL', 'formatOL', '|', 'indent', 'outdent', '|', 'insertLink', 'dp_media', 'insertTable', '|',
        'dpMedia', 'html', 'clearFormatting', 'fullscreen'],
      key:             'MC1D2D1G2lG4J4A14A7D3D6F6C2C3F3gSXSE1LHAFJVCXCLS==',
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
      imageEditButtons:  ['imageReplace', 'imageAlign', 'imageVAlign', 'imageCaption', 'imageRemove', '|', 'imageLink', 'linkOpen', 'linkEdit', 'linkRemove', '-', 'imageDisplay', 'imageStyle', 'imageAlt', 'imageSize'],
      imageDefaultWidth: 0
    };

    localOptions = Object.merge(defaultOptions, localOptions || {});

    const rte = $(field);
    rte.froalaEditor(localOptions);
    rte.on('froalaEditor.focus', () => {
      window.DeskPRO_Window.keyboardShortcuts.isPaused = true;
    });
    rte.on('froalaEditor.blur', () => {
      window.DeskPRO_Window.keyboardShortcuts.isPaused = false;
    });

    return rte;
  }
}

export default RteTextarea;
