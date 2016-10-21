import React, { PropTypes } from 'react';
import classNames from 'classnames';
import { ClickOut } from 'DeskPRO/Component/ClickOut';
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
    autoClose: false,
    autoOpen:  false
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
  }

  onMouseEnter = () => {
    if (this.props.autoOpen) {
      this.cancelTimeout();
      this.openPopup();
    }
  };

  onMouseLeave = () => {
    const { opened, autoClose } = this.props;

    if (opened || !autoClose) {
      return;
    }

    this.timeout = setTimeout(() => this.closePopup(), 100);
  };

  openPopup = () => {
    // const event = new Event('dpPopupOpen');
    // window.document.dispatchEvent(event);
    this.setState({
      isOpen: true
    });
  };

  closePopup = () => {
    this.setState({
      isOpen: false
    });
  };

  togglePopup = () => {
    if (this.state.isOpen) {
      this.closePopup();
    } else {
      this.openPopup();
    }
  };

  cancelTimeout = () => {
    if (this.timeout) {
      window.clearTimeout(this.timeout);
    }
  };

  renderBody() {
    const { content, positionAt, elementId } = this.props;

    return (
      <ClickOut onClickOut={this.closePopup}>
        <div id={elementId} className={classNames('ui', 'popup', positionAt, { visible: this.state.isOpen })}>
          {content}
        </div>
      </ClickOut>
      );
  }

  render() {
    const { isOpen } = this.state;
    const { children } = this.props;

    return (
      <div
        style={{ display: 'inline-block' }}
        className={classNames({ active: isOpen })}
        ref={(c) => { this.button = c; }}
        onClick={this.openPopup}
        onMouseEnter={this.onMouseEnter}
        onMouseLeave={this.onMouseLeave}
      >
        {children}
        <Detached
          isOpen={isOpen}
          positionTarget={this.button}
          {...this.props}
        >
          {this.renderBody()}
        </Detached>
      </div>
    );
  }
}
export default PopUp;
