import React from 'react';
import Frame from 'Ampliflux/common/components/Frame';

export default class WidgetAppBody extends React.Component {
  static propTypes = {
    onResize: React.PropTypes.func
  };

  render() {
    return (
      <div className="widget-container">
        Contents go here.
      </div>
    )
  }

  triggerResize() {
    if (this.props.onResize) {
      window.setTimeout(() => this.props.onResize(), 0);
    }
  }

  componentDidMount() {
    this.triggerResize();
  }

  componentDidUpdate() {
    this.triggerResize();
  }
}

export default class WidgetApp extends React.Component {

  static propTypes = {
    onClick: React.PropTypes.func,
    isVisible: React.PropTypes.bool
  };

  render() {
    const style = {
      marginRight: '14px'
    };

    return (
      <Frame ref="frame" style={ style } isVisible={this.props.isVisible} id="dp_widget_app">
        <WidgetAppBody onResize={() => this.refs.frame && this.refs.frame.autoFrameDimentions()} />
      </Frame>
    );
  }
}
