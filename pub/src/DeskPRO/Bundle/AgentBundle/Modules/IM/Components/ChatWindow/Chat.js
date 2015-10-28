import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import Spinner from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Spinner';

// components
import { Footer } from './Footer';
import { Header } from './Header';
import { MessageList } from './MessageList';
import { Offline } from './Offline';
import { SearchForm } from './SearchForm';

// messages
import { meSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/RecordStores/Selectors/meSelectors';
import { addMessage } from '../../Actions/messagesActions';

@connect(state => ({
  me: meSelector(state),
  messages: state.IM.messages,
  current: state.IM.chats.get('current')
}))
export class Chat extends React.Component {

  static propTypes = {
    me: PropTypes.object.isRequired,
    current: PropTypes.object.isRequired,
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

  messageList = () => {
    return (this.props.current.id)
      ? <MessageList current={this.props.current} searchQuery={this.state.searchQuery}/>
      : <Spinner width="40" height="40"/>;
  };

  handleType = (event) => {
    const oldState = this.state;
    const newState = {...oldState};
    newState.searchTyped = event.target.value;
    this.setState(newState);
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
    this.setState(newState);
  };

  handleAddMessage = (message) => {
    this.props.dispatch(addMessage(this.props.current.id, message, this.props.me));
  };

  searchForm() {
    return (this.state.searchShown) ? <SearchForm handleType={this.handleType} handleSearch={this.handleSearch} /> : null;
  }

  static typing() {
    return <div className="active-chat-user-typing">Jeniffer is typing a message <span id="typing">...</span></div>;
  }

  static offline() {
    return <Offline />;
  }

  render() {
    return (
      <div className="dropdown active-chat-dropdown" id="active-chat-dropdown">
        <Header toggleSearch={this.toggleSearch}/>
        { this.searchForm() }
        { this.messageList() }
        <Footer handleAddMessage={this.handleAddMessage}/>
      </div>
    );
  }
}
