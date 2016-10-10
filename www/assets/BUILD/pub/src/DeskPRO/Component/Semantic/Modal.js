import React, { PropTypes } from 'react';
import ReactModal from 'react-modal';
import classNames from 'classnames';

class Modal extends React.Component {

  static propTypes = {
    title:     PropTypes.string,
    isOpen:    PropTypes.bool,
    onClose:   PropTypes.func,
    children:  PropTypes.node,
    className: PropTypes.string
  };

  render() {
    const { isOpen, onClose, className, title, children } = this.props;
    const customStyles = {
      overlay: {
        zIndex:          1005,
        backgroundColor: 'rgba(0, 0, 0, 0.7)'
      }
    };

    return (
      <ReactModal
        isOpen={isOpen}
        onRequestClose={onClose}
        style={customStyles}
        className={classNames('widget-modal', className)}
      >
        {title && <div className="header">{title}</div>}
        <div className="content">
          {children}
        </div>
      </ReactModal>
    );
  }
}

export default Modal;
