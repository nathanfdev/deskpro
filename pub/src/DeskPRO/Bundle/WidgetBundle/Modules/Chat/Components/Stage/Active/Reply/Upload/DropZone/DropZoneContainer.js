import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { DropZone } from 'DeskPRO/Component/Uploader/DropZone';
import { DragOverlayListener } from 'DeskPRO/Component/Uploader/DragOverlayListener';
import { DropZoneOverlay } from './DropZoneOverlay';
import { uploadingFilesRepeatSelector } from '../../../../../../Selectors/chat';
import {
  addAttachment,
  addUploadingFile,
  removeUploadingFile,
  markUploadingFileFailed
} from '../../../../../../Actions/chatActions';
import { extension } from 'mime-types';
import moment from 'moment';

@connect(state => ({
  repeatFiles: uploadingFilesRepeatSelector(state)
}))
export class DropZoneContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func,
    children: PropTypes.node
  };

  onUploadStarted = (event, data) => {
    const { dispatch } = this.props;
    data.files.forEach(file => dispatch(addUploadingFile(file)));
  };

  onUploadSuccess = (event, response) => {
    const attachments = response.result && response.result.data || [];
    const { dispatch } = this.props;

    response.files.forEach(file => dispatch(removeUploadingFile(file)));
    attachments.forEach(attachment => dispatch(addAttachment(attachment)));
  };

  onUploadFail = (event, data) => {
    data.files.forEach(file => this.props.dispatch(markUploadingFileFailed(file)));
  };

  onPasteImage = (blob, imgSrc, contentType) => {
    const file = new File([blob], `clipboard_${moment().format()}.${extension(contentType)}`);
    this.refs.dropZone.pushFileToQueue(file);
  };

  render() {
    const { children } = this.props;
    const childProps = children.props;

    return (
      <DropZone ref="dropZone"
                uploadUrl={window.DP_HELPDESK_URL + 'portal/api/blobs/temp'}
                context={[window.widgetFrame.document, parent.window.document]}
                onSend={this.onUploadStarted}
                onSuccess={this.onUploadSuccess}
                onFail={this.onUploadFail} {...this.props}>

        <DragOverlayListener context={[parent.document, window.widgetFrame.document]}>
          <DropZoneOverlay />
        </DragOverlayListener>

        {React.cloneElement(children, {
          ...childProps,
          onPasteImage: this.onPasteImage
        })}
      </DropZone>
    );
  }
}
