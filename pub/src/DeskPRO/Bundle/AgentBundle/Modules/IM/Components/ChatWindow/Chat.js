import React from 'react';
import { Footer } from './Footer';
import { Header } from './Header';
import { MessageList } from './MessageList';
import { Offline } from './Offline';
import { SearchForm } from './SearchForm';
import { connect } from 'react-redux';
import { loadMessages, addMessage } from '../../Actions/imMessagesActions';
import { loadAllAgents } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Actions/agentsActions';
import { agentsSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Selectors/agentsSelectors';
import { meSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/RecordStores/Selectors/meSelectors';

@connect(state => ({
  me: meSelector(state),
  agents: agentsSelector(state),
  messages: state.IM.messages
}))
export class Chat extends React.Component {

  constructor(props) {
    super(props);
    this.state = {
      searchQuery: ''
    };
  }

  componentWillMount() {
    "use strict";
    this.props.dispatch(loadAllAgents());
    this.props.dispatch(loadMessages(this.props.current.id));
  }

  handleQuery = (event) => {
    "use strict";
    const oldState = this.state;
    const newState = {...oldState};
    newState.searchQuery = event.target.value;
    this.setState(newState);
  }

  handleSearch = () => {
    "use strict";
    this.props.dispatch(loadMessages(this.props.current.id, this.state.searchQuery));
  }

  render() {
    return (
      <div className="dropdown active-chat-dropdown" id="active-chat-dropdown">
        { this.head() }
        { this.searchForm() }
        <div className="chat-controls"><a href="#">Load old messages</a><a onClick={this.refresh} href="#">Refresh</a></div>
        <MessageList
          agents={this.props.agents}
          me={this.props.me}
          messages={this.props.messages.getIn(['chatMessages',this.props.current.id])}/>
        <Footer handleAddMessage={this.handleAddMessage}/>
      </div>
    );
  }


  refresh = () =>
  {
    "use strict";
    this.props.dispatch(loadMessages(this.props.current.id));
  };

  handleAddMessage = (message) => {
    "use strict";
    this.props.dispatch(addMessage(this.props.current.id, message, this.props.me));
  };

  searchForm() {
    "use strict";
    if(this.props.agents.size > 0) {
      return <SearchForm handleQuery={this.handleQuery} handleSearch={this.handleSearch} />
    }
  }

  head() {
    "use strict";
    if(this.props.agents.size > 0) {
      return <Header agents={this.props.agents} me={this.props.me} current={this.props.current}
                     handleCloseChat={this.props.handleCloseChat}/>
    }
  }



  static typing() {
    "use strict";
    return <div className="active-chat-user-typing">Jeniffer is typing a message <span id="typing">...</span></div>
  }

  static offline() {
    "use strict";
    return <Offline />
  }
}
