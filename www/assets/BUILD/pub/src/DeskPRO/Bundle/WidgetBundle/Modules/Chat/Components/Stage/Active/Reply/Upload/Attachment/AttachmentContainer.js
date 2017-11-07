import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import { removeAttachment } from '../../../../../../Actions/chatActions';
import {
  attachedImagesSelector,
  attachedImagesCountSelector,
  attachedFilesSelector
} from '../../../../../../Selectors/chat';

@connect(state => ({
  attachedImages:      attachedImagesSelector(state),
  attachedImagesCount: attachedImagesCountSelector(state),
  attachedFiles:       attachedFilesSelector(state)
}))
export class AttachmentContainer extends React.Component {

  static propTypes = {
    children: PropTypes.node,
    dispatch: PropTypes.func
  };

  onRemoveAttachment = attachment => {
    this.props.dispatch(removeAttachment(attachment));
  };

  render() {
    const props = this.props;
    const { children } = props;
    const childProps = children.props;

    return React.cloneElement(children, {
      ...props,
      ...childProps,

      onRemoveAttachment: this.onRemoveAttachment
    });
  }
}
