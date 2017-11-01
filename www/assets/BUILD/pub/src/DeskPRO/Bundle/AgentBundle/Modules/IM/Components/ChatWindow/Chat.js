import PropTypes from 'prop-types';
import React from 'react';
import Loader from 'react-loader';
import { connect } from 'react-redux';
import { ClickOut } from 'DeskPRO/Component/ClickOut';
import * as chatsActions from '../../Actions/chatsActions';
import uuid from 'node-uuid';

// components
import { Footer } from './Footer';
import { Header } from './Header';
import { MessageList } from './MessageList';
import { Offline } from './Offline';
import { SearchForm } from './SearchForm';

// messages
import { meSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/me';
import { addMessage } from '../../Actions/messagesActions';
import { agentsSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/agents';

@connect(state => ({
  me:       meSelector(state),
  messages: state.IM.messages,
  current:  state.IM.chats.get('current'),
  agents:   agentsSelector(state)
}))
export class Chat extends React.Component {
  static propTypes = {
    me:       PropTypes.object.isRequired,
    current:  PropTypes.object.isRequired,
    agents:   PropTypes.object.isRequired,
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
        const filteredAgents = current.agents.filter(agent => agent !== me.get('id'));
        const notMe = filteredAgents[0];
        online = agents.getIn([notMe, 'online']);
      }
    }
    return online;
  }

  messageList = () => {
    return (
      <div style={{ minHeight: 75 }}>
        <Loader loaded={this.props.current.id > 0} opacity={0} width={3} top="45%">
          <MessageList current={this.props.current} searchQuery={this.state.searchQuery} />
        </Loader>
      </div>
    );
  };

  handleType = (event) => {
    event.preventDefault();
    this.setState({
      searchTyped: event.target.value
    });
    if (event.target.value === '') {
      this.handleSearch(event);
    }
  };

  handleClear() {
    this.setState({
      searchTyped: '',
      searchQuery: ''
    });
  }

  handleOnClose = () => {
    this.props.dispatch(chatsActions.closeChat(this.props.current.id));
  };

  handleSearch = (event) => {
    event.preventDefault();
    this.setState({
      searchQuery: this.state.searchTyped
    });
  };

  toggleSearch = () => {
    const newState = {};
    newState.searchShown = !this.state.searchShown;
    if (!newState.searchShown) {
      newState.searchTyped = '';
      newState.searchQuery = '';
    }

    this.setState(newState);
  };

  handleAddMessage = message => {
    const { dispatch, current, me } = this.props;
    dispatch(addMessage(current.id, message, uuid(), me));
  };

  searchForm() {
    return (this.state.searchShown)
      ?
      <SearchForm
        handleClear={this.handleClear.bind(this)}
        handleType={this.handleType}
        handleSearch={this.handleSearch}
        searching={this.state.searchTyped}
      />
      : null;
  }

  static typing() {
    return <div className="active-chat-user-typing">Jeniffer is typing a message <span id="typing">...</span></div>;
  }

  offline() {
    if (this.isAgentChat()) {
      return <Offline online={this.isOnline()} />;
    }
  }

  render() {
    return (
    <ClickOut
      onClickOut={this.handleOnClose}
      additionalNodes={['#active-chat-search-clear', '.emoticon']}
    >
      <div className="dropdown active-chat-dropdown" id="active-chat-dropdown">
        <Header toggleSearch={this.toggleSearch} onClose={this.handleOnClose} online={this.isOnline()} />
        {this.searchForm()}
        {this.messageList()}
        {this.offline()}
        <Footer handleAddMessage={this.handleAddMessage} />
      </div>
    </ClickOut>

    );
  }
}
