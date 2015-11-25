import React from 'react';
import Frame from 'Ampliflux/common/components/Frame';

export default class TriggerButtonBody extends React.Component {

  static propTypes = {
    onResize: React.PropTypes.func,
    onClick: React.PropTypes.func
  };

  componentDidMount() {
    this.triggerResize();
  }

  componentDidUpdate() {
    this.triggerResize();
  }

  triggerResize() {
    if (this.props.onResize) {
      window.setTimeout(() => this.props.onResize(), 0);
    }
  }

  render() {
    return (
      <div className="trigger-button" onClick={() => this.props.onClick && this.props.onClick()}>
        <strong>Help</strong>
      </div>
    );
  }
}

export default class TriggerButton extends React.Component {

  static propTypes = {
    onClick: React.PropTypes.func,
    isVisible: React.PropTypes.bool
  };

  render() {
    const style = {
      margin: '14px'
    };

    return (
      <Frame ref="frame" style={ style } isVisible={this.props.isVisible} id="dp_widget_trigger">
        <TriggerButtonBody onResize={() => this.refs.frame && this.refs.frame.autoFrameDimentions()} onClick={() => this.props.onClick && this.props.onClick()} />
      </Frame>
    );
  }
}
