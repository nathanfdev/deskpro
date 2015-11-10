import React, { PropTypes } from 'react';
import Loader from 'react-loader';
import { connect } from 'react-redux';
import { ClickOut } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ClickOut';
import * as ui from '../../Actions/uiActions';
import jQuery from 'jquery';

// components
import { Footer } from './Footer';
import { Header } from './Header';
import { MessageList } from './MessageList';
import { Offline } from './Offline';
import { SearchForm } from './SearchForm';

// messages
import { meSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/RecordStores/Selectors/meSelectors';
import { addMessage } from '../../Actions/messagesActions';

import { agentsSelector, agentsStatusSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Selectors/agentsSelectors';

@connect(state => ({
  me: meSelector(state),
  messages: state.IM.messages,
  current: state.IM.chats.get('current'),
  agents: agentsSelector(state)
}))
export class Chat extends React.Component {

  static propTypes = {
    me: PropTypes.object.isRequired,
    current: PropTypes.object.isRequired,
    agents: PropTypes.object.isRequired,
    dispatch: PropTypes.func.isRequired
  };


  constructor(props) {
    super(props);
    this.state = {
      searchQuery: '',
      searchTyped: '',
      searchShown: false
    };
  }

  isAgentChat() {
    return this.props.current.chat_type === 'agent';
  }

  isOnline() {
    let online = false;
    if (this.isAgentChat()) {
      const { agents, current, me } = this.props;
      if (agents && agents.size > 0) {
        const filteredAgents = current.agents.filter(agent => agent !== me.get('id') );
        const notMe = filteredAgents[0];
        online = agents.getIn([notMe, 'online']);
      }
    }
    return online;
  }

  messageList = () => {
    return (
      <div style={{minHeight: 75}}>
        <Loader loaded={this.props.current.id} opacity={0} width={3} top="45%">
          <MessageList current={this.props.current} searchQuery={this.state.searchQuery}/>
        </Loader>
      </div>
    );
  };

  handleType = (event) => {
    const oldState = this.state;
    const newState = {...oldState};
    newState.searchTyped = event.target.value;
    this.setState(newState);
  };

  handleOnClose = (event) => {
    this.props.dispatch(ui.closeChat());
  };

  handleSearch = (event) => {
    event.preventDefault();
    const oldState = this.state;
    const newState = {...oldState};
    newState.searchQuery = oldState.searchTyped;
    this.setState(newState);
  };

  toggleSearch = () => {
    const oldState = this.state;
    const newState = {...oldState};
    newState.searchShown = !oldState.searchShown;
    if (!newState.searchShown) {
      newState.searchTyped = '';
      newState.searchQuery = '';
    }
    this.setState(newState);
  };

  handleAddMessage = (message) => {
    this.props.dispatch(addMessage(this.props.current.id, message, this.props.me));
  };

  searchForm() {
    return (this.state.searchShown)
      ?
      <SearchForm
        handleClear={this.handleClear}
        handleType={this.handleType}
        handleSearch={this.handleSearch}
        searching={this.state.searchTyped}/>
      : null;
  }

  static typing() {
    return <div className="active-chat-user-typing">Jeniffer is typing a message <span id="typing">...</span></div>;
  }

  offline() {
    if (this.isAgentChat()) {
      return <Offline online={this.isOnline()}/>;
    }
  }

  render() {
    return (
    <ClickOut
      onClickOut={this.handleOnClose}
      ignoreNodes={[jQuery('#active-chat-search-clear')]}
      >
      <div className="dropdown active-chat-dropdown" id="active-chat-dropdown">
        <Header toggleSearch={this.toggleSearch} onClose={this.handleOnClose} online={this.isOnline()}/>
        { this.searchForm() }
        { this.messageList() }
        { this.offline() }
        <Footer handleAddMessage={this.handleAddMessage}/>
      </div>
    </ClickOut>

    );
  }
}
