import React, { PropTypes } from 'react';
import { Detached } from 'DeskPRO/Component/Detached';

/**
 * <a href="#" onClick={this.showModal}>click to open</a>
 * <Modal ref="modal"
 *        title="Test modal title">
 *   <div>
 *     Testing modal window
 *   </div>
 * </Modal>
 *
 *
 * showModal = () => {
 *   this.refs.modal.open();
 * };
 *
 */

export class Modal extends React.Component {

  static propTypes = {
    title: PropTypes.string,
    onConfirm: PropTypes.func,
    onCancel: PropTypes.func,
    confirmTitle: PropTypes.string,
    cancelTitle: PropTypes.string,
    confirmVisible: PropTypes.bool,
    cancelVisible: PropTypes.bool,
    zIndex: PropTypes.number
  };

  static defaultProps = {
    title: 'Modal Title',
    confirmTitle: 'Save',
    cancelTitle: 'Cancel',
    confirmVisible: true,
    cancelVisible: true,
    zIndex: 1005
  };

  constructor(props) {
    super(props);

    this.state = {
      isOpen: false
    };
  }open() {
  if (this.state.isOpen) return;
  this.setState({isOpen: true});
}

  close() {
    if (!this.state.isOpen) return;
    this.setState({isOpen: false});
  }

  confirmClick = (event) => {
    event.preventDefault();
    if (!this.state.isOpen) return;
    this.setState({isOpen: false});
    this.props.onConfirm && this.props.onConfirm();
  };

  innerClick = (event) => {
    event.preventDefault();
    event.stopPropagation();
  };

  cancelClick = (event) => {
    event.preventDefault();
    if (!this.state.isOpen) return;
    this.setState({isOpen: false});
    this.props.onCancel && this.props.onCancel();
  };

  renderBody() {
    const { title, confirmVisible, cancelVisible, isMini, children } = this.props;

    let className = 'dpw--modal';
    if (isMini) {
      className += ' mini';
    }
    if (!confirmVisible && !cancelVisible) {
      className += ' no-footer';
    }

    let style = {};
    if (this.props.zIndex) {
      style.zIndex = this.props.zIndex;
    }

    return (
      <div className="modal" style={style}>
        <div className="modal-fade-screen" onClick={this.cancelClick}>
          <div className="modal-inner" onClick={this.innerClick}>
            <div className="modal-close" onClick={this.cancelClick}></div>
            <h1>{title}</h1>
            <p className="modal-content">
              {children}
            </p>
            {this.renderFooter()}
          </div>
        </div>
      </div>
    );
  }

  render() {
    const { isOpen } = this.state;

    return (
      <Detached>
        {isOpen ? this.renderBody() : null}
      </Detached>
    );
  }

  renderFooter() {
    if (!this.props.confirmVisible && !this.props.cancelVisible) return null;

    return (
      <div className="modal-footer">
        <hr/>
        {this.renderButton('confirm')}
        {this.renderButton('cancel')}
      </div>
    );
  }

  renderButton(type) {
    invariant(Modal.defaultProps[type + 'Visible'] !== undefined, 'Invalid modal button type');
    if (!this.props[type + 'Visible']) return null;

    const className = 'popup-button ' + type;
    return (
      <button className={className} onClick={this[type + 'Click']}>
        {this.props[type + 'Title']}
      </button>
    );
  }
}