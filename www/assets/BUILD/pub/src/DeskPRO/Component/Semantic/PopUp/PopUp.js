import PropTypes from 'prop-types';
import React from 'react';
import classNames from 'classnames';
import { ClickOut } from 'DeskPRO/Component/ClickOut';
import { Detached } from '../../Positioned/Detached';

class PopUp extends React.Component {

  static propTypes = {
    opened:     PropTypes.bool,
    elementId:  PropTypes.string,
    positionAt: PropTypes.string,

    content: PropTypes.oneOfType([
      PropTypes.string,
      PropTypes.node
    ]).isRequired,

    children:             PropTypes.oneOfType([PropTypes.object, PropTypes.array]),
    autoClose:            PropTypes.bool,
    autoOpen:             PropTypes.bool,
    allowCloseOnClickOut: PropTypes.bool,
    manual:               PropTypes.bool,
    clickOut:             PropTypes.bool,
    className:            PropTypes.string,
    style:                PropTypes.object,
    innerClassName:       PropTypes.string,
  };

  static defaultProps = {
    className:      '',
    innerClassName: '',

    autoClose:            false,
    autoOpen:             false,
    allowCloseOnClickOut: true,
    manual:               false,
    clickOut:             true,
  };

  constructor(props) {
    super(props);
    this.state = {
      isOpen: this.props.opened
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

  clickOutClosePopup = () => {
    if (this.props.allowCloseOnClickOut) {
      this.closePopup();
    }
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
    const { content, positionAt, elementId, innerClassName } = this.props;

    if (this.props.clickOut) {
      return (
        <ClickOut onClickOut={this.clickOutClosePopup} additionalNodes={['.ReactModalPortal']}>
          <div id={elementId} className={classNames('ui', 'popup', positionAt, { visible: this.state.isOpen }, innerClassName)}>
            {content}
          </div>
        </ClickOut>
      );
    }
    return (<div id={elementId} className={classNames('ui', 'popup', positionAt, { visible: this.state.isOpen }, innerClassName)}>
      {content}
    </div>);
  }

  render() {
    const { isOpen } = this.state;
    const { children, className, style } = this.props;

    let onClick = function () {};
    if (!this.props.manual) {
      onClick = this.openPopup;
    }

    return (
      <div
        className={classNames({ active: isOpen }, className)}
        style={style}
        ref={(c) => { this.button = c; }}
        onClick={onClick}
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
