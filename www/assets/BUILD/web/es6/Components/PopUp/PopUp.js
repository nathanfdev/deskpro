import React, { PropTypes } from 'react';
import classNames from 'classnames';
import { Detached } from 'Components/Positioned/Detached';

class PopUp extends React.Component {
  static propTypes = {
    opened: PropTypes.bool,
    onOpen: PropTypes.func,
    elementId: PropTypes.string,
    content: PropTypes.oneOfType([
      PropTypes.string,
      PropTypes.node
    ]).isRequired,
    id: PropTypes.number.isRequired,
  };
  static defaultProps = {
    onOpen: function() {}
  };

  constructor(props) {
    super(props);
    this.state = {
      isOpen: !!this.props.opened
    }
  }

  renderBody() {
    const { content, positionAt, elementId } = this.props;
    return <div
      id={elementId}
      className={classNames('ui', 'popup', positionAt, {'visible' : this.state.isOpen})}
      onMouseEnter={this.cancelTimeout.bind(this)}
      onMouseLeave={this.onMouseLeave.bind(this)}
    >{content}</div>;
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
    this.timeout = setTimeout(function () {
      self.setState({
        isOpen: false
      })
    }, 500);
  }

  cancelTimeout() {
    if (this.timeout) {
      window.clearTimeout(this.timeout);
    }
  }

  render() {
    const { isOpen } = this.state;
    const { id, children } = this.props;

    return (
      <div
        style={{display: 'inline-block'}}
        ref={`button${id}`}
        onMouseEnter={this.onMouseEnter.bind(this)}
        onMouseLeave={this.onMouseLeave.bind(this)}
      >
        {children}
        <Detached isOpen={isOpen}
          positionTarget={this.refs[`button${id}`]}
          {...this.props} >
          {isOpen ? this.renderBody() : null}
        </Detached>
      </div>
    );
  }
}
export default PopUp;