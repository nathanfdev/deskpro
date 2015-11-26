import React, { PropTypes } from 'react';
import Frame from 'Ampliflux/common/components/Frame';

export default class TriggerButtonBody extends React.Component {

  static propTypes = {
    onResize: PropTypes.func,
    onClick: PropTypes.func
  };

  componentDidMount() {
    this.triggerResize();
  }

  componentDidUpdate() {
    this.triggerResize();
  }

  triggerResize() {
    const { onResize } = this.props;
    if (onResize) {
      window.setTimeout(() => onResize(), 0);
    }
  }

  render() {
    return (
      <div className="trigger-button" onClick={this.props.onClick}>
        <strong>Help</strong>
      </div>
    );
  }
}

export default class TriggerButton extends React.Component {

  static propTypes = {
    onClick: PropTypes.func,
    isVisible: PropTypes.bool
  };

  render() {
    const { isVisible, onClick } = this.props;
    const style = {
      margin: '14px'
    };

    return (
      <Frame ref="frame" style={style} isVisible={isVisible}>
        <TriggerButtonBody onResize={() => this.refs.frame && this.refs.frame.autoFrameDimensions()} onClick={onClick} />
      </Frame>
    );
  }
}
