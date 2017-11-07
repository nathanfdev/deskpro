import React from 'react';
import { Router, Route, Redirect } from 'react-router';
import WidgetFrameContainer from './WidgetFrameContainer';
import { WidgetContent } from './Parts/WidgetContent';
import { WidgetHeaderContainer } from './Parts/Header/WidgetHeaderContainer';
import { WidgetBodyContainer } from './Parts/Body/WidgetBodyContainer';
import { WidgetFooter } from './Parts/WidgetFooter';
import {
  ChatApp,
  ChatBeginContainer,
  ChatBeginSimple,
  ChatBeginConversation,
  ChatBeginForm,
  ChatPollingContainer,
  ChatActive
} from '../../../Chat/Components/index';
import { TicketApp, TicketForm, TicketFormSubmitted } from '../../../Ticket/Components/index';
import { history } from '../../../../Services/history';

export default class Widget extends React.Component {

  componentDidMount() {
    window.widgetFrame = parent.window.widget_iframe;
  }


  render() {
    return (
      <WidgetFrameContainer>
        <WidgetContent>
          <WidgetHeaderContainer />
          <WidgetBodyContainer>
            <Router history={history}>
              <Redirect from="/" to="chat" />
              <Route path="chat" component={ChatApp}>
                <Route path="begin" component={ChatBeginContainer}>
                  <Route name="chat_begin_simple" path="simple" component={ChatBeginSimple} />
                  <Route name="chat_begin_conversation" path="conversation" component={ChatBeginConversation} />
                  <Route name="chat_begin_form" path="form" component={ChatBeginForm} />
                </Route>
                <Route component={ChatPollingContainer}>
                  <Route name="chat_active" path="active" component={ChatActive} />
                </Route>
              </Route>
              <Route path="ticket" component={TicketApp}>
                <Route name="ticket_form" path="form" component={TicketForm} />
                <Route name="ticket_form_submitted" path="form_submitted" component={TicketFormSubmitted} />
              </Route>
            </Router>
          </WidgetBodyContainer>
          <WidgetFooter />
        </WidgetContent>
      </WidgetFrameContainer>
    );
  }
}
