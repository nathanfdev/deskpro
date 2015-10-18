import React, { PropTypes } from 'react';
// components
import { Footer } from './Footer';
import { Header } from './Header';
import { MessageList } from './MessageList';
import { Offline } from './Offline';
import { SearchForm } from './SearchForm';
import { connect } from 'react-redux';
// messages
import { loadMessages, addMessage } from '../../Actions/imMessagesActions';

@connect(state => ({
  me: state.Application.user,
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
      searchQuery: ''
    };
  }

  messageList = () => {
    return (this.props.current.id) ? <MessageList current={this.props.current} /> : <img src="/web/spinner.gif" style={{width: 40 + 'px', height: 40 + 'px'}}/>;
  };

  handleQuery = (event) => {
    const oldState = this.state;
    const newState = {...oldState};
    newState.searchQuery = event.target.value;
    this.setState(newState);
  };

  handleSearch = () => {
    this.props.dispatch(loadMessages(this.props.current.id, this.state.searchQuery));
  };

  refresh = () => {
    this.props.dispatch(loadMessages(this.props.current.id));
  };

  handleAddMessage = (message) => {
    this.props.dispatch(addMessage(this.props.current.id, message, this.props.me));
  };

  searchForm() {
    return <SearchForm handleQuery={this.handleQuery} handleSearch={this.handleSearch} />;
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
        <Header />
        { this.searchForm() }
        <div className="chat-controls"><a href="#">Load old messages</a><a onClick={this.refresh} href="#">Refresh</a></div>
        { this.messageList() }
        <Footer handleAddMessage={this.handleAddMessage}/>
      </div>
    );
  }
}
