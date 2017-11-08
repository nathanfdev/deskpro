import PropTypes from 'prop-types';
import React from 'react';
import ReactDOM from 'react-dom';
import { connect } from 'react-redux';
import { Message } from './Message';
import { loadMessages, markMessages } from '../../Actions/messagesActions';
import { agentsSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/agents';
import { meSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/me';
import Loader from 'react-loader';

@connect(state => ({
  me:               meSelector(state),
  agents:           agentsSelector(state),
  messages:         state.IM.messages,
  loadingMessages:  state.IM.messages.get('loadingMessages'),
  updatingMessages: state.IM.messages.get('updatingMessages')
}))
export class MessageList extends React.Component {

  static propTypes = {
    me:               PropTypes.object.isRequired,
    agents:           PropTypes.object.isRequired,
    current:          PropTypes.object.isRequired,
    messages:         PropTypes.object.isRequired,
    loadingMessages:  PropTypes.bool.isRequired,
    updatingMessages: PropTypes.bool.isRequired,
    dispatch:         PropTypes.func.isRequired,
    searchQuery:      PropTypes.string.isRequired
  };


  componentDidMount() {
    this.refresh();
    this.shouldScrollBottom = true;
    this.firstScroll = true;
  }

  componentWillReceiveProps(newProps) {
    if (newProps.searchQuery !== this.props.searchQuery) {
      this.props.dispatch(loadMessages(this.props.current.id, newProps.searchQuery));
    }
    this.props = newProps;
  }

  componentWillUpdate = () => {
    const node = ReactDOM.findDOMNode(this.refs.list);
    this.shouldScrollBottom = node && (node.scrollTop + node.offsetHeight === node.scrollHeight);
    if (this.firstScroll === true && node) {
      this.firstScroll = false;
      this.shouldScrollBottom = true;
    }
  };

  componentDidUpdate = () => {
    this.scroll();
    this.markNewMessages();
  };


  getPath = () => {
    let path;
    if (!this.props.searchQuery) {
      path = ['chatMessages', this.props.current.id];
    } else {
      path = ['searchMessages', this.props.current.id];
    }
    return path;
  };

  scroll = () => {
    const node = ReactDOM.findDOMNode(this.refs.list);
    if (this.shouldScrollBottom && node) {
      node.scrollTop = node.scrollHeight;
    }
  };


  markNewMessages() {
    if (!this.props.updatingMessages) {
      const ids = [];
      const uuids = [];
      const { messages, dispatch } = this.props;

      const msg = messages.hasIn(this.getPath()) ? messages.getIn(this.getPath()).messages : [];
      msg.map((message) => {
        if (message.id && message.status < 2 && message.person !== this.props.me.get('id')) {
          ids.push(message.id);
          uuids.push(message.uuid);
        }
      });
      if (ids.length > 0) {
        dispatch(markMessages(ids, uuids, this.props.current.id));
      }
    }
  }

  loadOld = () => {
    const { messages } = this.props;
    const page = messages.hasIn(this.getPath()) ? messages.getIn(this.getPath()).page : 1;
    this.props.dispatch(loadMessages(this.props.current.id, this.props.searchQuery, page + 1));
  };

  controls = () => {
    const { messages } = this.props;
    const page = messages.hasIn(this.getPath()) ? messages.getIn(this.getPath()).page : 1;
    const pages = messages.hasIn(this.getPath()) ? messages.getIn(this.getPath()).pages : 1;
    return (
      <li className="chat-controls">
        {page < pages ? <a href="#" onClick={this.loadOld}>Load old messages</a> : null}
      </li>
    );
  };

  refresh = () => {
    this.props.dispatch(loadMessages(this.props.current.id, this.props.searchQuery));
    this.markNewMessages();
  };


  renderList(msg) {
    let previous = false;
    return (
      <ul ref="list" className="chat-message-list">
        {this.controls()}
        {
          msg.map((message, index) => {
            const result = (
              <Message
                key={index}
                message={message}
                size={msg.size}
                current={parseInt(index, 10)}
                previousMessage={previous}
                agents={this.props.agents}
                me={this.props.me}
              />
            );
            previous = message;
            return result;
          })
        }
      </ul>
    );
  }

  renderEmpty = () => {
    return (
      <ul ref="list" className="chat-message-list">
        <li className="chat-controls">
          <a>Sorry, nothing found here</a>
        </li>
      </ul>
    );
  };

  render() {
    const { messages } = this.props;
    let msg = messages.hasIn(this.getPath()) ? messages.getIn(this.getPath()).messages : [];
    msg = msg.sort((first, second) => {
      return first.timestamp - second.timestamp;
    });
    const loaded = !this.props.loadingMessages || msg.size > 0;
    return (
       <Loader loaded={loaded}>
         {msg.size > 0 ? this.renderList(msg) : this.renderEmpty()}
      </Loader>
    );
  }
}
