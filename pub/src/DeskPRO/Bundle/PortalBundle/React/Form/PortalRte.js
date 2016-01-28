import React, { PropTypes } from 'react';
import ReactDOM from 'react-dom';
import { RteEditor } from 'DeskPRO/Component/Rte/RteEditor';
import { DropZone } from 'DeskPRO/Component/Uploader/DropZone';
import { DragOverlayListener } from 'DeskPRO/Component/Uploader/DragOverlayListener';
import { PasteCatcher } from 'DeskPRO/Component/Uploader/PasteCatcher';
import { portalUrlGenerator } from '../../Http/PortalUrlGenerator';

export class PortalRte extends React.Component {

  static propTypes = {
    className: PropTypes.string,
    widgetOptions: PropTypes.object,
    $toolbarContainer: PropTypes.object,
    $textTextarea: PropTypes.object
  };

  onChangeMessage = value => {
    this.props.$textTextarea.val(value);
  };

  onUploadSuccess = (event, response) => {
    const attachment = response.result && response.result.blob || {};
    console.log(attachment);
  };

  onPasteImage = (blob, src) => {
    const editor = this.refs.input;

    editor.focus();
    editor.pasteHtml(`<img src="${src}">`);
  };

  getNode() {
    return ReactDOM.findDOMNode(this);
  }

  render() {
    const { widgetOptions, $textTextarea, $toolbarContainer, className } = this.props;
    const context = widgetOptions.context || document;

    const ownerDocument = $textTextarea.context.ownerDocument;
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
          value={$textTextarea.val()}
          onChange={this.onChangeMessage}
          getPasteCatcher={() => this.refs.pasteCatcher}
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
        <DropZone getExternalInput={() => this.refs.fileUpload}
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
          <PasteCatcher ref="pasteCatcher" context={window} onPasteImage={this.onPasteImage} />
        </DropZone>
      </div>
    );
  }
}
