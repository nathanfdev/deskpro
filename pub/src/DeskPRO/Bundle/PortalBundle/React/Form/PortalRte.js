import React, { PropTypes } from 'react';
import RteInput from 'DeskPRO/Component/Rte/RteInput';
import { DropZone } from 'DeskPRO/Component/Uploader/DropZone';
import { portalUrlGenerator } from 'DeskPRO/Bundle/PortalBundle/Http/PortalUrlGenerator';

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

  render() {
    const { widgetOptions, $textTextarea, $toolbarContainer, className } = this.props;
    const context = widgetOptions.context || document;

    const ownerDocument = $textTextarea.context.ownerDocument;
    const contentWindow = ownerDocument.defaultView;

    return (
      <div>
        <RteInput
          className={className}
          value={$textTextarea.val()}
          onChange={this.onChangeMessage}
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

        <input type="submit" ref="fileUpload" style={{display: 'none'}} />
        <DropZone getExternalInput={() => this.refs.fileUpload}
                  uploadUrl={portalUrlGenerator.path('/') + 'dpblob'}
                  context={context}
                  onSend={this.onUploadStarted}
                  onSuccess={this.onUploadSuccess}
                  onFail={this.onUploadFail}
                  dropNode={'.attach-file'}>

          <div className="dp-medium-rte-wrapper-overlay">
            <h1>Drag your file here.</h1>
          </div>
        </DropZone>
      </div>
    );
  }
}
