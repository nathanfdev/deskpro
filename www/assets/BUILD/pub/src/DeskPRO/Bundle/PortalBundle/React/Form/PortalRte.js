import React, { PropTypes } from 'react';
import ReactDOM from 'react-dom';
import { RteEditor } from 'DeskPRO/Component/Rte/RteEditor';
import { DropZone } from 'DeskPRO/Component/Uploader/DropZone';
import { portalPhrases } from 'DeskPRO/Bundle/PortalBundle/PortalPhrases';
import { DragOverlayListener } from 'DeskPRO/Component/Uploader/DragOverlayListener';
import { portalUrlGenerator } from '../../Http/PortalUrlGenerator';
import uniqueId from 'lodash/utility/uniqueId';
import $ from 'jquery';

export class PortalRte extends React.Component {

  static propTypes = {
    className:         PropTypes.string,
    widgetOptions:     PropTypes.object,
    $toolbarContainer: PropTypes.object,
    $textarea:         PropTypes.object,

    // Inline attachment form prototype must be suppled
    // if inline attachments (e.g. pasting, dragging images etc) is to be supported.
    $inlineAttachProto: PropTypes.object
  };

  constructor(props) {
    super(props);

    this.fileCounter = 0;
  }

  componentDidMount() {
    const editor = this.refs.input;
    const { $textarea } = this.props;

    $textarea.closest('form').on('reset', () => {
      editor.setContent('');
    });
    $textarea.on('change', () => {
      if (editor.getContent() !== $textarea.val()) {
        editor.setContent($textarea.val());
      }
    });
  }

  onChangeMessage = value => {
    this.props.$textarea.val(value).trigger('change');
  };

  onUploadSubmit = (event, data) => {
    if (data.files[0].type.indexOf('image') === -1) {
      window.alert('Allowed images only.');
      return false;
    }

    return true;
  };

  onUploadStarted = (event, data) => {
    const editor = this.refs.input;
    editor.focus();

    const file   = data.files[0];
    const urlObj = window.URL || window.webkitURL;
    const imgUrl = urlObj.createObjectURL(file);

    file.id = ++this.fileCounter;
    editor.pasteHtml(`<img src="${imgUrl}" data-paste-id="${file.id}">`);
    this.onChangeMessage(editor.getContent());
  };

  onUploadSuccess = (event, response) => {
    const { $textarea, $inlineAttachProto } = this.props;
    const pasteId = response.files[0].id;
    const $image  = $(`img[data-paste-id=${pasteId}]`, this.getNode());
    const editor  = this.refs.input;
    const blob    = response.result && response.result.blob;

    if (blob) {
      $image.removeAttr('data-paste-id').attr('src', blob.url);
      this.onChangeMessage(editor.getContent());

      if (this.props.$inlineAttachProto) {
        const $inlineField = $($inlineAttachProto.data('prototype').replace(/__name__/g, uniqueId('inline_field_')));
        $inlineField.find('input').val(blob.authcode);
        $inlineField.insertAfter($textarea);
      }
    } else {
      $image.remove();
      this.onChangeMessage(editor.getContent());
    }
  };

  onUploadFail = (event, response) => {
    const pasteId = response.files[0].id;
    const $image  = $(`img[data-paste-id=${pasteId}]`, this.getNode());

    $image.remove();

    const editor = this.refs.input;
    this.onChangeMessage(editor.getContent());
  };

  onPasteImage = file => {
    this.refs.dropZone.pushFileToQueue(file);
  };

  getNode() {
    return ReactDOM.findDOMNode(this);
  }

  render() {
    const { widgetOptions, $textarea, $toolbarContainer, className } = this.props;
    const context = widgetOptions.context || document;

    const ownerDocument = $textarea.context.ownerDocument;
    const contentWindow = ownerDocument.defaultView;

    const params = {};
    if (window.dp_get_csrf_token) {
      params['file[_dp_csrf_token]'] = window.dp_get_csrf_token();
    }

    return (
      <div>
        <RteEditor
          ref="input"
          className={className}
          value={$textarea.val()}
          onChange={this.onChangeMessage}
          onPasteImage={this.onPasteImage}
          options={{
            contentWindow,
            ownerDocument,
            toolbar: {
              buttons: [
                'bold',
                'italic',
                'underline',
                'anchor',
                'unorderedlist',
                'orderedlist',
                'quote',
                'pre',
                'removeFormat'
              ],
              static: true,
              sticky: true,
              align:  'left',

              updateOnEmptySelection: true,
              relativeContainer:      $toolbarContainer.get(0)
            },
            targetBlank:  true,
            buttonLabels: 'fontawesome'
          }}
        />

        <input type="submit" ref="fileUpload" name="file[blob]" style={{ display: 'none' }} />
        <DropZone
          ref="dropZone"
          getExternalInput={() => this.refs.fileUpload}
          uploadUrl={`${portalUrlGenerator.path('/')}dpblob`}
          uploadParams={params}
          context={context}
          onSubmit={this.onUploadSubmit}
          onSend={this.onUploadStarted}
          onSuccess={this.onUploadSuccess}
          onFail={this.onUploadFail}
        >
          <DragOverlayListener context={context}>
            <div className="dp-medium-rte-wrapper-overlay">
              <h1>{portalPhrases.get('portal.forms.label_drag_overlay')}</h1>
            </div>
          </DragOverlayListener>
        </DropZone>
      </div>
    );
  }
}
