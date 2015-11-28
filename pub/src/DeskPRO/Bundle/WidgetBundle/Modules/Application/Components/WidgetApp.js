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
  ChatPollingContainer,
  ChatActive,
  ChatWaiting,
  ChatDone
} from '../../Chat/Components/index';
import history from '../../../Services/history';
import store from '../../../Services/store';

export class WidgetAppBody extends React.Component {

  static propTypes = {
    onResize: PropTypes.func,
    onClose: PropTypes.func
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
    const { onClose } = this.props;

    return (
      <Provider store={store}>
        <Widget>
          <WidgetHeader title="Acme Corp. Chat and a long name lorel ipsum dolor" onClose={onClose} />
          <WidgetBody>
            <Router history={history}>
              <Redirect from="/" to="chat"/>
              <Route path="chat" component={ChatApp}>
                <Route path="begin" component={ChatBeginContainer}>
                  <Route name="chat_begin_simple" path="simple" component={ChatBeginSimple} />
                  <Route name="chat_begin_conversation" path="conversation" component={ChatBeginConversation} />
                  <Route name="chat_begin_form" path="form" component={ChatBeginForm} />
                </Route>
                <Route component={ChatPollingContainer}>
                  <Route name="chat_waiting" path="waiting" component={ChatWaiting} />
                  <Route name="chat_active" path="active" component={ChatActive} />
                  <Route name="chat_done" path="done" component={ChatDone} />
                </Route>
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
    isVisible: PropTypes.bool
  };

  render() {
    const style = {
      height: '100%'
    };

    return (
      <Frame ref="frame" style={style} isVisible={this.props.isVisible}>
        <WidgetAppBody {...this.props} onResize={() => this.refs.frame && this.refs.frame.autoFrameDimensions()} />
      </Frame>
    );
  }
}
