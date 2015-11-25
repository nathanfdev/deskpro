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
    const { onResize } = this.props;
    if (onResize) {
      window.setTimeout(() => onResize(), 0);
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
    onClick: PropTypes.func,
    isVisible: PropTypes.bool
  };

  render() {
    const { isVisible } = this.props;
    const style = {
      marginRight: '14px'
    };

    return (
      <Frame ref="frame" id="dp_widget_app" style={style} isVisible={isVisible}>
        <WidgetAppBody onResize={() => this.refs.frame && this.refs.frame.autoFrameDimensions()} />
      </Frame>
    );
  }
}
