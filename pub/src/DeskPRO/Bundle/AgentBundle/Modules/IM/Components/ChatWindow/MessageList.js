import React, { PropTypes } from 'react';
import { Message } from './Message';

import { connect } from 'react-redux';
import { loadMessages, addMessage } from '../../Actions/imMessagesActions';
import { loadAllAgents } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Actions/agentsActions'
import { agentsSelector, agentsStatusSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Selectors/agentsSelectors';

@connect(state => ({
  me: state.Application.user,
  agents: agentsSelector(state),
  agentsStatus: agentsStatusSelector(state),
  messages: state.IM.messages
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
    this.props.dispatch(loadAllAgents());
    this.props.dispatch(loadMessages(this.props.current.id));
  }

  componentWillReceiveProps(newProps) {
    if (newProps.searchQuery !== this.props.searchQuery) {
      this.props.dispatch(loadMessages(this.props.current.id, newProps.searchQuery));
    }
    
  }

  render() {
    const messages = this.props.messages.getIn(['chatMessages', this.props.current.id]) || [];
    return (messages.length > 0 ) ? (
      <ul className="chat-message-list">
        {
          messages.map((message, index) => {
            return (<Message
              key={index}
              message={message}
              agents={this.props.agents}
              me={this.props.me}/>);
          })
        }
      </ul>
    ) : <img src="/web/spinner.gif" style={{width: 40 + 'px', height: 40 + 'px'}}/>;
  }
}