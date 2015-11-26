import React, { PropTypes } from 'react';
import { Provider } from 'react-redux';
import { Router, Route, Redirect } from 'react-router';
import Frame from 'Ampliflux/common/components/Frame';
import { Widget, WidgetHeader, WidgetBody } from './Widget/index';
import {
  ChatApp,
  ChatBeginContainer,
  ChatBeginSimple,
  ChatBeginConversation,
  ChatBeginForm,
  ChatActive,
  ChatWaiting,
  ChatDone
} from '../../Chat/Components/index';

export class WidgetAppBody extends React.Component {

  static propTypes = {
    onResize: PropTypes.func,
    store: PropTypes.object
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
      <Provider store={this.props.store}>
        <Widget>
          <WidgetHeader />
          <WidgetBody>
            <Router>
              <Redirect from="/" to="chat"/>
              <Route path="chat" component={ChatApp}>
                <Route path="begin" component={ChatBeginContainer}>
                  <Route name="chat_begin_simple" path="simple" component={ChatBeginSimple} />
                  <Route name="chat_begin_conversation" path="conversation" component={ChatBeginConversation} />
                  <Route name="chat_begin_form" path="form" component={ChatBeginForm} />
                </Route>
                <Route name="chat_waiting" path="waiting" component={ChatWaiting} />
                <Route name="chat_active" path="active" component={ChatActive} />
                <Route name="chat_done" path="done" component={ChatDone} />
              </Route>
            </Router>
          </WidgetBody>
        </Widget>
      </Provider>
    );
  }
}

export class WidgetApp extends React.Component {

  static propTypes = {
    onClick: PropTypes.func,
    isVisible: PropTypes.bool,
    store: PropTypes.object
  };

  render() {
    const { isVisible, store } = this.props;
    const style = {
      height: '100%'
    };

    return (
      <Frame ref="frame" style={style} isVisible={isVisible}>
        <WidgetAppBody store={store} onResize={() => this.refs.frame && this.refs.frame.autoFrameDimensions()} />
      </Frame>
    );
  }
}
