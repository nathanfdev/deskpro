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
  ChatValidation,
  ChatEmailValidationContainer,
  ChatLoginContainer,
  ChatPollingContainer,
  ChatActive
} from '../../../Chat/Components/index';
import { TicketApp, TicketForm, TicketFormSubmitted } from '../../../Ticket/Components/index';
import { history } from '../../../../Services/history';

const routes = [
  <Redirect key="1" from="/" to="chat" />,
  <Route key="2" path="chat" component={ChatApp}>
    <Route path="begin" component={ChatBeginContainer}>
      <Route name="chat_begin_simple" path="simple" component={ChatBeginSimple} />
      <Route name="chat_begin_conversation" path="conversation" component={ChatBeginConversation} />
      <Route name="chat_begin_form" path="form" component={ChatBeginForm} />
    </Route>
    <Route component={ChatPollingContainer}>
      <Route path="validation" component={ChatValidation} >
        <Route name="chat_validation_email" path="email" component={ChatEmailValidationContainer} />
        <Route name="chat_validation_login" path="login" component={ChatLoginContainer} />
      </Route>
      <Route name="chat_active" path="active" component={ChatActive} />
    </Route>
  </Route>,
  <Route key="3" path="ticket" component={TicketApp}>
    <Route name="ticket_form" path="form" component={TicketForm} />
    <Route name="ticket_form_submitted" path="form_submitted" component={TicketFormSubmitted} />
  </Route>
];

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
              {routes}
            </Router>
          </WidgetBodyContainer>
          <WidgetFooter />
        </WidgetContent>
      </WidgetFrameContainer>
    );
  }
}
