import React, { PropTypes } from 'react';
import classNames from 'classnames';
import { Detached } from '../../Positioned/Detached';

class PopUp extends React.Component {
  static propTypes = {
    opened:     PropTypes.bool,
    onOpen:     PropTypes.func,
    elementId:  PropTypes.string,
    positionAt: PropTypes.string,
    content:    PropTypes.oneOfType([
      PropTypes.string,
      PropTypes.node
    ]).isRequired,
    id:        PropTypes.number.isRequired,
    children:  PropTypes.any,
    autoClose: PropTypes.bool,
    autoOpen:  PropTypes.bool
  };
  static defaultProps = {
    onOpen() {},
    autoClose: true,
    autoOpen:  true
  };

  constructor(props) {
    super(props);
    this.state = {
      isOpen: !!this.props.opened
    };
    if (this.props.autoClose) {
      window.document.addEventListener('dpPopupOpen', () => {
        this.closePopup();
      });
    }
    this.cancelTimeout = this.cancelTimeout.bind(this);
    this.onMouseEnter = this.onMouseEnter.bind(this);
    this.onMouseLeave = this.onMouseLeave.bind(this);
    this.openPopup = this.openPopup.bind(this);
    this.togglePopup = this.togglePopup.bind(this);
  }

  onMouseEnter() {
    if (this.props.autoOpen) {
      this.cancelTimeout();
      this.openPopup();
    }
  }

  onMouseLeave() {
    if (this.props.opened) {
      return;
    }
    const self = this;
    this.timeout = setTimeout(() => {
      self.closePopup();
    }, 500);
  }

  openPopup() {
    const event = new Event('dpPopupOpen');
    window.document.dispatchEvent(event);
    this.setState({
      isOpen: true
    });
  }

  closePopup() {
    this.setState({
      isOpen: false
    });
  }

  togglePopup() {
    if (this.state.isOpen) {
      this.closePopup();
    } else {
      this.openPopup();
    }
  }

  cancelTimeout() {
    if (this.timeout) {
      window.clearTimeout(this.timeout);
    }
  }

  renderBody() {
    const { content, positionAt, elementId } = this.props;
    return (<div
      id={elementId}
      className={classNames('ui', 'popup', positionAt, { visible: this.state.isOpen })}
      onMouseEnter={this.cancelTimeout}
      onMouseLeave={this.onMouseLeave}
    >{content}</div>);
  }

  render() {
    const { isOpen } = this.state;
    const { id, children } = this.props;


    return (
      <div
        style={{ display: 'inline-block' }}
        className={classNames({ active: isOpen })}
        ref={`button${id}`}
        onClick={this.openPopup}
        onMouseEnter={this.onMouseEnter}
        onMouseLeave={this.onMouseLeave}
      >{children}
        <Detached
          isOpen={isOpen}
          positionTarget={this.refs[`button${id}`]}
          {...this.props}
        >
          {isOpen ? this.renderBody() : null}
        </Detached>
      </div>
    );
  }
}
export default PopUp;
