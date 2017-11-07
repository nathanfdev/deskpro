import PropTypes from 'prop-types';
import React from 'react';
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
    title:          PropTypes.string,
    onConfirm:      PropTypes.func,
    onCancel:       PropTypes.func,
    confirmTitle:   PropTypes.string,
    cancelTitle:    PropTypes.string,
    confirmVisible: PropTypes.bool,
    cancelVisible:  PropTypes.bool,
    isMini:         PropTypes.bool,
    zIndex:         PropTypes.number
  };

  static defaultProps = {
    title:          'cowabunga!',
    confirmTitle:   'Save',
    cancelTitle:    'Cancel',
    confirmVisible: true,
    cancelVisible:  true,
    isMini:         true,
    zIndex:         1005
  };

  constructor(props) {
    super(props);

    this.state = {
      isOpen: false
    };
  }

  open() {
    if (this.state.isOpen) return;
    this.setState({ isOpen: true });
  }

  close() {
    if (!this.state.isOpen) return;
    this.setState({ isOpen: false });
  }

  confirmClick = (event) => {
    event.preventDefault();
    if (!this.state.isOpen) return;
    this.setState({ isOpen: false });
    this.props.onConfirm && this.props.onConfirm();
  };

  cancelClick = (event) => {
    event.preventDefault();
    if (!this.state.isOpen) return;
    this.setState({ isOpen: false });
    this.props.onCancel && this.props.onCancel();
  };

  coverClick = (event, b, c, d, e) => {
    event.stopPropagation();
    event.nativeEvent.stopImmediatePropagation();
    if (event.target.className !== 'cover') {
      return false;
    }
    this.cancelClick(event);
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

    const style = {};
    if (this.props.zIndex) {
      style.zIndex = this.props.zIndex;
    }

    return (
      <div className="dpw-site-cover" onClick={this.coverClick} style={style}>
        <section className={className}>
          <header>
            <h1>
              {title}
            </h1>
            <div className="controls">
              <a href="#" onClick={this.cancelClick}><i className="fa fa-times" /></a>
            </div>
          </header>
          <div className={`${isMini && 'mini-'}popup-content`}>
            {children}
          </div>
          {this.renderFooter()}
        </section>
      </div>
    );
  }

  renderFooter() {
    if (!this.props.confirmVisible && !this.props.cancelVisible) return null;

    return (
      <div className="popup-footer">
        <hr />
        {this.renderButton('confirm')}
        {this.renderButton('cancel')}
      </div>
    );
  }

  renderButton(type) {
    invariant(Modal.defaultProps[`${type}Visible`] !== undefined, 'Invalid modal button type');
    if (!this.props[`${type}Visible`]) return null;

    const className = `popup-button ${type}`;
    return (
      <button className={className} onClick={this[`${type}Click`]}>
        {this.props[`${type}Title`]}
      </button>
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
}
