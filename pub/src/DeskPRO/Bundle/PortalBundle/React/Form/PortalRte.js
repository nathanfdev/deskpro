import React, { PropTypes } from 'react';
import { RteInput } from 'DeskPRO/Component/Rte/RteInput';
import { DropZone } from 'DeskPRO/Component/Uploader/DropZone';
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

        <input type="submit" ref="fileUpload" name="file[blob]" style={{display: 'none'}} />
        <DropZone getExternalInput={() => this.refs.fileUpload}
                  uploadUrl={portalUrlGenerator.path('/') + 'dpblob'}
                  uploadParams={params}
                  context={context}
                  onSend={this.onUploadStarted}
                  onSuccess={this.onUploadSuccess}
                  onFail={this.onUploadFail}
                  dropNode={'.attach-file'}>

          <div className="dp-medium-rte-wrapper-overlay">
            <h1>Drag your file in here.</h1>
          </div>
        </DropZone>
      </div>
    );
  }
}
