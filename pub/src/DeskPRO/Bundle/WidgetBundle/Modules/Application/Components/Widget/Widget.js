import React from 'react';
import { Router, Route, Redirect } from 'react-router';
import { WidgetFrameContainer } from './WidgetFrameContainer';
import { WidgetContent } from './Parts/WidgetContent';
import { WidgetHeaderContainer } from './Parts/Header/WidgetHeaderContainer';
import { WidgetBody } from './Parts/WidgetBody';
import { WidgetFooter } from './Parts/WidgetFooter';
import {
  ChatApp,
  ChatBeginContainer,
  ChatBeginSimple,
  ChatBeginConversation,
  ChatBeginForm,
  ChatPollingContainer,
  ChatActive,
  ChatWaiting
} from '../../../Chat/Components/index';
import history from '../../../../Services/history';

export class Widget extends React.Component {

  componentDidMount() {
    window.widgetFrame = parent.window.widget_iframe;
  }

  render() {
    return (
      <WidgetFrameContainer>
        <WidgetContent>
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
        </WidgetContent>
      </WidgetFrameContainer>
    );
  }
}
