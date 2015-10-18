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
      searchQuery: '',
      searchTyped: '',
      searchShown: false,
    };
  }

  messageList = () => {
    return (this.props.current.id)
      ? <MessageList current={this.props.current} searchQuery={this.state.searchQuery}/>
      : <img src="/web/spinner.gif" style={{width: 40 + 'px', height: 40 + 'px'}}/>;
  };

  handleType = (event) => {
    const oldState = this.state;
    const newState = {...oldState};
    newState.searchTyped = event.target.value;
    this.setState(newState);
  };

  handleSearch = () => {
    const oldState = this.state;
    const newState = {...oldState};
    newState.searchQuery = oldState.searchTyped;
    this.setState(newState);
  };

  toggleSearch = () => {
    const oldState = this.state;
    const newState = {...oldState};
    newState.searchShown = !oldState.searchShown;
    console.log(newState);
    this.setState(newState);
  };

  refresh = () => {
    this.props.dispatch(loadMessages(this.props.current.id));
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
        <div className="chat-controls"><a href="#">Load old messages</a><a onClick={this.refresh} href="#">Refresh</a></div>
        { this.messageList() }
        <Footer handleAddMessage={this.handleAddMessage}/>
      </div>
    );
  }
}
