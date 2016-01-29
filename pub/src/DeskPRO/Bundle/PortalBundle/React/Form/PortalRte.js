import React, { PropTypes } from 'react';
import ReactDOM from 'react-dom';
import { RteEditor } from 'DeskPRO/Component/Rte/RteEditor';
import { DropZone } from 'DeskPRO/Component/Uploader/DropZone';
import { DragOverlayListener } from 'DeskPRO/Component/Uploader/DragOverlayListener';
import { portalUrlGenerator } from '../../Http/PortalUrlGenerator';
import { pageWidgetEmitter } from 'DeskPRO/Component/PageWidget/PageWidgetEmitter';
import $ from 'jquery';

export class PortalRte extends React.Component {

  static propTypes = {
    className: PropTypes.string,
    widgetOptions: PropTypes.object,
    $toolbarContainer: PropTypes.object,
    $textarea: PropTypes.object
  };

  constructor(props) {
    super(props);
    this.fileCounter = 0;
  }

  onChangeMessage = value => {
    this.props.$textarea.val(value);
  };

  onUploadStarted = (event, data) => {
    const editor = this.refs.input;
    editor.focus();

    const file = data.files[0];
    if (file.type.indexOf('image') === -1) {
      pageWidgetEmitter.emit('rteFileUpload', file);
      return;
    }

    const urlObj = window.URL || window.webkitURL;
    const imgUrl = urlObj.createObjectURL(file);

    file.id = ++this.fileCounter;
    editor.pasteHtml(`<img src="${imgUrl}" data-paste-id="${file.id}">`);
  };

  onUploadSuccess = (event, response) => {
    const { $textarea } = this.props;
    const file = response.files[0];
    const pasteId = response.files[0].id;
    const $image = $('img[data-paste-id=' + pasteId + ']', this.getNode());
    const $editor = $(ReactDOM.findDOMNode(this.refs.input));

    if (file.type.indexOf('image') === -1) {
      return;
    }

    const blob = response.result && response.result.blob;
    if (blob) {
      $image.removeAttr('data-paste-id').attr('src', blob.url);

      const blobPath = $textarea.data('blob-path');
      $(`<input type="hidden" name="${blobPath}[${blob.id}][blob_auth]" />`).val(blob.authcode).insertAfter($editor);
    } else {
      $image.remove();
    }
  };

  onUploadFail = (event, response) => {
    const pasteId = response.files[0].id;
    const $image = $('img[data-paste-id=' + pasteId + ']', this.getNode());

    $image.remove();
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
            contentWindow: contentWindow,
            ownerDocument: ownerDocument,
            toolbar: {
              buttons: ['bold', 'italic', 'underline', 'anchor', 'unorderedlist', 'orderedlist', 'quote', 'pre', 'removeFormat'],
              static: true,
              sticky: true,
              updateOnEmptySelection: true,
              align: 'left',
              relativeContainer: $toolbarContainer.get(0)
            },
            targetBlank: true,
            buttonLabels: 'fontawesome'
          }}/>

        <input type="submit" ref="fileUpload" name="file[blob]" style={{display: 'none'}} />
        <DropZone
          ref="dropZone"
          getExternalInput={() => this.refs.fileUpload}
          uploadUrl={portalUrlGenerator.path('/') + 'dpblob'}
          uploadParams={params}
          context={context}
          onSend={this.onUploadStarted}
          onSuccess={this.onUploadSuccess}
          onFail={this.onUploadFail}>

          <DragOverlayListener context={context}>
            <div className="dp-medium-rte-wrapper-overlay">
              <h1>Drag your file in here.</h1>
            </div>
          </DragOverlayListener>
        </DropZone>
      </div>
    );
  }
}
