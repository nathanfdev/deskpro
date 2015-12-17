import React, { PropTypes } from 'react';
import { Provider } from 'react-redux';
import { Router, Route, Redirect } from 'react-router';
import Frame from 'Ampliflux/common/components/Frame';
import { Widget, WidgetHeaderContainer, WidgetBody, WidgetFooter } from './Widget/index';
import {
  ChatApp,
  ChatBeginContainer,
  ChatBeginSimple,
  ChatBeginConversation,
  ChatBeginForm,
  ChatPollingContainer,
  ChatActive,
  ChatWaiting
} from '../../Chat/Components/index';
import history from '../../../Services/history';
import store from '../../../Services/store';
import $ from 'jquery';
import debounce from 'lodash/function/debounce';
import { windowResize } from '../Actions/dpWindowActions';

export class WidgetAppBody extends React.Component {

  static propTypes = {
    onResize: PropTypes.func
  };

  constructor(props) {
    super(props);

    this.onWindowResize();
    this.onResize = debounce(() => {
      this.onWindowResize();
    }, 350);
  }

  componentDidMount() {
    window.widgetFrame = parent.window.widget_iframe;
    $(window.parent).on('resize', this.onResize);

    this.triggerResize();
  }

  componentDidUpdate() {
    this.triggerResize();
  }

  componentWillUnmount() {
    $(window.parent).off('resize', this.onResize);
  }

  onWindowResize() {
    store.dispatch(windowResize(
      $(window.widgetFrame).width(),
      $(window.widgetFrame).height()
    ));

    const { onResize } = this.props;
    if (onResize) {
      onResize();
    }
  }

  triggerResize() {
    window.setTimeout(() => this.onWindowResize(), 0);
  }

  render() {
    return (
      <Provider store={store}>
        <Widget>
          <WidgetHeaderContainer />
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
                </Route>
              </Route>
            </Router>
          </WidgetBody>
          <WidgetFooter />
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
    return (
      <Frame ref="frame"
             name="widget_iframe"
             frameStyles={{height: '100%'}}
             containerStyles={{right: 0}}
             isVisible={this.props.isVisible}>

        <WidgetAppBody ref="body"
                       onResize={() => this.refs.frame && this.refs.frame.autoFrameDimensions()} {...this.props} />
      </Frame>
    );
  }
}
