import React, { PropTypes } from 'react';
import { Router, Route, Redirect } from 'react-router';
import Frame from 'Ampliflux/common/components/Frame';
import { ChatApp } from '../../Chat/Components/ChatApp';

export default class WidgetAppBody extends React.Component {

  static propTypes = {
    onResize: PropTypes.func
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
      <div className="widget-container">
        <Router>
          <Redirect from="/" to="chat"/>
          <Route name="chat" path="chat" component={ChatApp}/>
        </Router>
      </div>
    );
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
