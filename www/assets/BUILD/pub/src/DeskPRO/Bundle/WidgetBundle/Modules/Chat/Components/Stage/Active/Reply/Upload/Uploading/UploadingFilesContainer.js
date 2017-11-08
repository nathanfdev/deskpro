import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import { uploadingFilesSelector, uploadingFilesFailedSelector } from '../../../../../../Selectors/chat';
import { repeatUploadingFile, removeUploadingFile } from '../../../../../../Actions/chatActions';

@connect(state => ({
  files:  uploadingFilesSelector(state),
  failed: uploadingFilesFailedSelector(state)
}))
export class UploadingFilesContainer extends React.Component {

  static propTypes = {
    children: PropTypes.node,
    dispatch: PropTypes.func
  };

  onRemove = file => {
    this.props.dispatch(removeUploadingFile(file));
  };

  onRepeat = file => {
    this.props.dispatch(repeatUploadingFile(file));
  };

  render() {
    const props = this.props;
    const { children } = props;
    const childProps = children.props;

    return React.cloneElement(children, {
      ...props,
      ...childProps,

      onRepeat: this.onRepeat,
      onRemove: this.onRemove
    });
  }
}
