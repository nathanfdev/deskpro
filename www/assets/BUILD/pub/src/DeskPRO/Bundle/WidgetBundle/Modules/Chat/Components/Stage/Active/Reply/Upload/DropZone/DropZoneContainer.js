import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { DropZone } from 'DeskPRO/Component/Uploader/DropZone';
import { DropZoneOverlay } from './DropZoneOverlay';
import { uploadingFilesRepeatSelector } from '../../../../../../Selectors/chat';
import {
  addAttachment,
  addUploadingFile,
  removeUploadingFile,
  markUploadingFileFailed
} from '../../../../../../Actions/chatActions';

@connect(state => ({
  repeatFiles: uploadingFilesRepeatSelector(state)
}))
export class DropZoneContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func
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

  render() {
    return (
      <DropZone uploadUrl={window.DP_HELPDESK_URL + 'portal/api/blobs/temp'}
                context={[window.widgetFrame.document, parent.window.document]}
                onSend={this.onUploadStarted}
                onSuccess={this.onUploadSuccess}
                onFail={this.onUploadFail} {...this.props}>

        <DropZoneOverlay />
      </DropZone>
    );
  }
}
