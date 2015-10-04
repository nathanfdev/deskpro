import React from 'react';
import { Footer } from './Footer';
import { Header } from './Header';
import { MessageList } from './MessageList';
import { Offline } from './Offline';
import { SearchForm } from './SearchForm';
import { connect } from 'react-redux';
import { loadMessages } from '../../Actions/imMessagesActions';
import { loadAllAgents } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Actions/agentsActions'
import { agentsSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Selectors/agentsSelectors';

@connect(state => ({
  me: state.user,
  agents: agentsSelector(state),
  messages: state.IM.messages.chatMessages.messages
}))
export class Chat extends React.Component {

  componentWillMount() {
    "use strict";
    this.props.dispatch(loadAllAgents());
    this.props.dispatch(loadMessages(this.props.current.id));
  }

  render() {
    return (
      <div className="dropdown active-chat-dropdown" id="active-chat-dropdown">
        { this.head() }


        <SearchForm />

        <div className="chat-controls"><a href="#">Load old messages</a></div>

        <MessageList messages={this.props.messages[this.props.current.id]}/>

        //<div className="active-chat-user-typing">Jeniffer is typing a message <span id="typing">...</span></div>

        <Offline />

        <Footer />
      </div>
    );
  }

  head() {
    "use strict";
    if(this.props.agents.size > 0) {
      return <Header agents={this.props.agents} me={this.props.me} current={this.props.current}
                     handleCloseChat={this.props.handleCloseChat}/>
    }
  }
}
