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
    id:       PropTypes.number.isRequired,
    children: PropTypes.any
  };
  static defaultProps = {
    onOpen() {}
  };

  constructor(props) {
    super(props);
    this.state = {
      isOpen: !!this.props.opened
    };
    this.cancelTimeout = this.cancelTimeout.bind(this);
    this.onMouseEnter = this.onMouseEnter.bind(this);
    this.onMouseLeave = this.onMouseLeave.bind(this);
  }

  onMouseEnter() {
    this.cancelTimeout();
    this.setState({
      isOpen: true
    });
  }

  onMouseLeave() {
    if (this.props.opened) {
      return;
    }
    const self = this;
    this.timeout = setTimeout(() => {
      self.setState({
        isOpen: false
      });
    }, 500);
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
        ref={`button${id}`}
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
