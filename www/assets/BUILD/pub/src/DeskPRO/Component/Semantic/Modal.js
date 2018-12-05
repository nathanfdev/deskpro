import PropTypes from 'prop-types';
import React from 'react';
import ReactModal from 'react-modal';
import classNames from 'classnames';

class Modal extends React.Component {

  static propTypes = {
    title:              PropTypes.string,
    isOpen:             PropTypes.bool,
    onClose:            PropTypes.func,
    onCloseButtonClick: PropTypes.func,
    children:           PropTypes.node,
    className:          PropTypes.string,
    overlayStyles:      PropTypes.object,
    contentStyles:      PropTypes.object
  };

  static defaultProps = {
    overlayStyles: {},
    contentStyles: { top: '25%', bottom: 'auto' }
  };

  render() {
    const { isOpen, onClose, className, title, children, onCloseButtonClick } = this.props;
    const { overlayStyles, contentStyles } = this.props;
    const customStyles = {
      overlay: {
        zIndex:          999999,
        backgroundColor: 'rgba(0, 0, 0, 0.7)',
        ...overlayStyles
      },
      content: contentStyles
    };

    // fixed height size, to add vertical scroll bar
    let contentWrapperStyles = {};
    if (contentStyles.bottom !== 'auto') {
      contentWrapperStyles = {
        ...contentWrapperStyles,
        position: 'absolute',
        top:      '30px',
        bottom:   0,
        left:     0,
        right:    0,
        overflow: 'scroll'
      };
    }

    return (
      <ReactModal
        isOpen={isOpen}
        onRequestClose={onClose}
        style={customStyles}
        className={classNames('widget-modal', className)}
      >
        {title &&
          <div className="header">
            {title}
            {onCloseButtonClick && <span className="close-btn" onClick={onCloseButtonClick}>[ x ]</span>}
          </div>}
        <div className="content" style={contentWrapperStyles}>
          {children}
        </div>
      </ReactModal>
    );
  }
}

export default Modal;
