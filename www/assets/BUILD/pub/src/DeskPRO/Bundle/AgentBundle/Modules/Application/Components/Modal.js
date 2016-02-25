import React, { PropTypes } from 'react';
import ReactDOM from 'react-dom';
import $ from 'jquery';
import invariant from 'invariant';
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
    isMini: PropTypes.bool
  };

  static defaultProps = {
    title: 'cowabunga!',
    confirmTitle: 'Save',
    cancelTitle: 'Cancel',
    confirmVisible: true,
    cancelVisible: true,
    isMini: true
  };

  constructor(props) {
    super(props);

    this.state = {
      isOpen: false
    };
  }

  open() {
    if (this.state.isOpen) return;
    this.setState({isOpen: true});
  }

  close() {
    if (!this.state.isOpen) return;
    this.setState({isOpen: false});
  }

  confirmClick = (event) => {
    if (!this.state.isOpen) return;
    this.setState({isOpen: false});
    this.props.onConfirm && this.props.onConfirm();
  };

  cancelClick = (event) => {
    if (!this.state.isOpen) return;
    this.setState({isOpen: false});
    this.props.onCancel && this.props.onCancel();
  };

  coverClick = (event) => {
    event.stopPropagation();
    return this.cancelClick(event);
  };

  render() {
    const { title, content, confirmVisible, cancelVisible, isMini, children } = this.props;
    const { isOpen } = this.state;

    let className = 'dpw--modal';
    if (isMini) {
      className += ' mini';
    }
    if (!confirmVisible && !cancelVisible) {
      className += ' no-footer';
    }

    return (
      <Detached>
        {isOpen ? [
          <div key="cover" className="cover" onClick={this.coverClick}></div>,
          <section key="modal" className={className}>
            <header>
              <h1>
                {title}
              </h1>
              <div className="controls">
                <a href="#" onClick={this.cancelClick}><i className="fa fa-times"></i></a>
              </div>
            </header>
            <div className="mini-popup-content">
              {children}
            </div>
            {this.renderFooter()}
          </section>
        ] : null}
      </Detached>
    );
  }

  renderFooter() {
    if (!this.props.confirmVisible && !this.props.cancelVisible) return null;

    return (
      <div className="popup-footer">
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
      <a href="#" className={className} onClick={this[type + 'Click']}>
        {this.props[type + 'Title']}
      </a>
    );
  }
}
