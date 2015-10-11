import React from 'react';
// components
import { Footer } from './Footer';
import { Header } from './Header';
import { MessageList } from './MessageList';
import { Offline } from './Offline';
import { SearchForm } from './SearchForm';
import { connect } from 'react-redux';
// messages
import { loadMessages, addMessage } from '../../Actions/imMessagesActions';

// agents
import { loadAllAgents } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Actions/agentsActions'
import { agentsSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Selectors/agentsSelectors';

//teams
import { loadMyAgentTeams } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Actions/agentTeamsActions'
import { myAgentTeamsSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Selectors/agentTeamsSelectors';

// departmetns
import { loadDepartments, loadAllDepartments }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Actions/departmentsActions';
import { departmentsSelector, allDepartmentsSelector }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Selectors/departmentsSelectors';

@connect(state => ({
  me: state.Application.user,
  agents: agentsSelector(state),
  departments: allDepartmentsSelector(state),
  teams: myAgentTeamsSelector(state),
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
    this.props.dispatch(loadAllAgents());
    this.props.dispatch(loadMyAgentTeams());
    this.props.dispatch(loadAllDepartments());
    this.props.dispatch(loadMessages(this.props.current.id));
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

  head() {
    if(this.props.agents.size > 0) {
      return <Header
        agents={this.props.agents}
        teams={this.props.teams}
        departments={this.props.departments}
        me={this.props.me}
        current={this.props.current}
        handleCloseChat={this.props.handleCloseChat}
        />
    }
  }

  handleQuery = (event) => {
    const oldState = this.state;
    const newState = {...oldState};
    newState.searchQuery = event.target.value;
    this.setState(newState);
  };

  handleSearch = () => {
    this.props.dispatch(loadMessages(this.props.current.id, this.state.searchQuery));
  };

  refresh = () =>
  {
    this.props.dispatch(loadMessages(this.props.current.id));
  };

  handleAddMessage = (message) => {
    this.props.dispatch(addMessage(this.props.current.id, message, this.props.me));
  };

  searchForm() {
    if(this.props.agents.size > 0) {
      return <SearchForm handleQuery={this.handleQuery} handleSearch={this.handleSearch} />
    }
  }

  static typing() {
    return <div className="active-chat-user-typing">Jeniffer is typing a message <span id="typing">...</span></div>
  }

  static offline() {
    return <Offline />
  }
}
