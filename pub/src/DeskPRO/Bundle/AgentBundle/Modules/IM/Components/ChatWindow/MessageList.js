import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import Spinner from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Spinner';
import { Message } from './Message';
import { loadMessages } from '../../Actions/imMessagesActions';
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

  controls = () => {
    return <div className="chat-controls"><a href="#">Load old messages</a><a onClick={this.refresh} href="#">Refresh</a></div>;
  };

  refresh = () => {
    this.props.dispatch(loadMessages(this.props.current.id));
  };

  render() {
    const messages = this.props.messages.getIn(['chatMessages', this.props.current.id]) || [];
    return (messages.length > 0 ) ? (
      <div>
      {this.controls()}
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
      </div>
    ) : <Spinner width="40" height="40" />;
  }
}