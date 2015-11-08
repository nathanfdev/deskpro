import React, { PropTypes } from 'react';
import ReactDOM from 'react-dom';
import { connect } from 'react-redux';
import { Message } from './Message';
import { loadMessages, markMessages, refreshCounts } from '../../Actions/messagesActions';
import { agentsSelector, agentsStatusSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Selectors/agentsSelectors';
import { meSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/RecordStores/Selectors/meSelectors';
import Loader from 'react-loader';

@connect(state => ({
  me: meSelector(state),
  agents: agentsSelector(state),
  agentsStatus: agentsStatusSelector(state),
  messages: state.IM.messages,
  page: state.IM.messages.get('page'),
  pages: state.IM.messages.get('pages'),
  messagesStatus: state.IM.messages.loadingMessages
}))
export class MessageList extends React.Component {

  static propTypes = {
    me: PropTypes.object.isRequired,
    agents: PropTypes.object.isRequired,
    agentsStatus: PropTypes.object.isRequired,
    current: PropTypes.object.isRequired,
    messages: PropTypes.object.isRequired,
    dispatch: PropTypes.func.isRequired,
    searchQuery: PropTypes.string.isRequired
  };

  componentDidMount() {
    this.refresh();
    const interval = setInterval(this.refresh, 15000);
    const countsInterval = setInterval(() => this.props.dispatch(refreshCounts()), 15000);
    this.state = {
      interval: interval,
      countsInterval: countsInterval
    };
    this.shouldScrollBottom = true;
  }

  componentWillReceiveProps(newProps) {
    if (newProps.searchQuery !== this.props.searchQuery) {
      this.props.dispatch(loadMessages(this.props.current.id, newProps.searchQuery));
    }
  }

  componentWillUpdate = () => {
    const node = ReactDOM.findDOMNode(this.refs.list);
    this.shouldScrollBottom = node.scrollTop + node.offsetHeight === node.scrollHeight;
  };

  componentDidUpdate = () => {
    if (this.shouldScrollBottom) {
      const node = ReactDOM.findDOMNode(this.refs.list);
      node.scrollTop = node.scrollHeight;
    }
  };

  componentWillUnmount() {
    clearInterval(this.state.interval);
    clearInterval(this.state.countsInterval);
  }

  markNewMessages() {
    const ids = [];
    const { messages } = this.props;
    const path = ['chatMessages', this.props.current.id];
    const msg = messages.getIn(path) ? messages.getIn(path).messages : [];
    msg.map((message) => {
      if (message.status < 1 && message.person_id !== this.props.me.get('id')) {
        ids.push(message.id);
      }
    });
    if (ids.length > 0) {
      this.props.dispatch(markMessages(ids));
    }
  }

  loadOld = () => {
    const { messages } = this.props;
    const path = ['chatMessages', this.props.current.id];
    const page = messages.getIn(path) ? messages.getIn(path).page : 1;
    this.props.dispatch(loadMessages(this.props.current.id, this.props.searchQuery, page + 1));
  };

  controls = () => {
    const { messages } = this.props;
    const path = ['chatMessages', this.props.current.id];
    const page = messages.getIn(path) ? messages.getIn(path).page : 1;
    const pages = messages.getIn(path) ? messages.getIn(path).pages : 1;
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

  render() {
    const { messages } = this.props;
    const path = ['chatMessages', this.props.current.id];
    const msg = messages.getIn(path) ? messages.getIn(path).messages : [];
    msg.sort((first, second) => {
      return first.id - second.id;
    });
    const loaded = !this.props.messages.loadingMessages;
    return (
       <Loader loaded={loaded}>

        <ul ref="list" className="chat-message-list">
          { this.controls() }
          {
            msg.map((message, index) => {
              return (<Message
                  key={index}
                  message={message}
                  agents={this.props.agents}
                  me={this.props.me}/>);
            })
          }
        </ul>
      </Loader>
    );
  }
}