import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { DropZone } from './DropZone';
import { addAttachment, addUploadingFile, removeUploadingFile } from '../../../../../../Actions/chatActions';

@connect()
export class DropZoneContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func
  };

  onUploadedStarted = (event, data) => {
    const { dispatch } = this.props;
    data.files.forEach(file => dispatch(addUploadingFile(file)));
  };

  onUploadedSuccess = (event, response) => {
    const attachments = response.result && response.result.data || [];
    const { dispatch } = this.props;

    attachments.forEach(attachment => dispatch(addAttachment(attachment)));
    this.removeFilesFromQueue(response);
  };

  onUploadedFail = (event, data) => {
    this.removeFilesFromQueue(data);
  };

  removeFilesFromQueue(data) {
    data.files.forEach(file => this.props.dispatch(removeUploadingFile(file)));
  }

  render() {
    return (
      <DropZone uploadUrl={window.DP_HELPDESK_URL + 'portal/api/blobs/temp'}
                context={[window.widgetFrame.document, parent.window.document]}
                onSend={this.onUploadedStarted}
                onSuccess={this.onUploadedSuccess}
                onFail={this.onUploadedFail} {...this.props} />
    );
  }
}
