import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { uploadingFilesSelector } from '../../../../../../Selectors/chat';
import { removeUploadingFile } from '../../../../../../Actions/chatActions';

@connect(state => ({
  uploadingFiles: uploadingFilesSelector(state)
}))
export class UploadingFilesContainer extends React.Component {

  static propTypes = {
    children: PropTypes.node,
    dispatch: PropTypes.func
  };

  onRetry = file => {
    this.props.dispatch(removeUploadingFile(file));
  };

  render() {
    const props = this.props;
    const { children } = props;
    const childProps = children.props;

    return React.cloneElement(children, {
      ...props,
      ...childProps,

      onRetry: this.onRetry
    });
  }
}
