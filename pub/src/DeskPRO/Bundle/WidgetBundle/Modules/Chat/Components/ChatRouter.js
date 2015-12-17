import React from 'react';
import { Route } from 'react-router';
import { ChatApp } from './ChatApp';
import { ChatBeginContainer } from './Stage/Begin/ChatBeginContainer';
import { ChatBeginSimple } from './Stage/Begin/ChatBeginSimple';
import { ChatBeginConversation } from './Stage/Begin/Conversation/ChatBeginConversation';
import { ChatBeginForm } from './Stage/Begin/Form/ChatBeginForm';
import { ChatPollingContainer } from './Stage/ChatPollingContainer';
import { ChatWaiting } from './Stage/Waiting/ChatWaiting';
import { ChatActive } from './Stage/Active/ChatActive';

export class ChatRouter extends React.Component {

  render() {
    return (
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
    );
  }
}
