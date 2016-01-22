import React, { PropTypes } from 'react';
import { DragOverlayListener } from 'DeskPRO/Component/Uploader/DragOverlayListener';
import RteInput from 'DeskPRO/Component/Rte/RteInput';

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

        <DragOverlayListener context={context} dropNode={'.attach-file'}>
          <div className="dp-medium-rte-wrapper-overlay">
            <h1>Drag your file here.</h1>
          </div>
        </DragOverlayListener>
      </div>
    );
  }
}
