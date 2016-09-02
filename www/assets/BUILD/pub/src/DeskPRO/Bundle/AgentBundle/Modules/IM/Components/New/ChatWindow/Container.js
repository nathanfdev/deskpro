import React, { PropTypes } from 'react';
import { Detached } from 'DeskPRO/Component/Positioned/Detached';
import { ClickOut } from 'DeskPRO/Component/ClickOut';
import { Scrollable } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Scrollable';
import MessageList from './MessageList';

class Container extends React.Component {
  static propTypes = {
    me:          PropTypes.object.isRequired,
    chats:       PropTypes.object.isRequired,
    agents:      PropTypes.object.isRequired,
    departments: PropTypes.object.isRequired,
    current:     PropTypes.number.isRequired,
    isOpen:      PropTypes.bool.isRequired,
    children:    PropTypes.oneOfType([PropTypes.object, PropTypes.array])
  };

  constructor(props) {
    super(props);
    this.state = {
      isOpen: !!this.props.isOpen
    };

    this.closePopup = this.closePopup.bind(this);
  }

  getAgentHeader(chat) {
    let agentId;
    for (const id of chat.get('agents')) {
      if (id !== this.props.me.get('id')) {
        agentId = id;
        break;
      }
    }
    return this.props.agents.getIn([agentId, 'name']);
  }

  getDepartmentHeader(chat) {
    return this.props.departments.getIn([chat.get('departments')[0], 'title']);
  }

  getHeader() {
    const current = this.props.chats.get(this.props.current);
    switch (current.get('chat_type')) {
      case 'agent':
        return this.getAgentHeader(current);
      case 'department':
        return this.getDepartmentHeader(current);
      default:
        return 'some im';
    }
  }

  closePopup() {
    this.setState({
      isOpen: false
    });
  }

  render() {
    return (
      <Detached
        isOpen={this.state.isOpen}
        positionTarget={document.getElementById(`chat-${this.props.chats.getIn([this.props.current, 'id'])}`)}
        positionMy="left-40 top+3"
      >
        <ClickOut onClickOut={this.closePopup}>
          <div className="ui popup left bottom im chat drawer">
            <div className="header">{this.getHeader()}</div>
            <div className="box">
              <Scrollable vertical>
                <MessageList {...this.props} />
              </Scrollable>
            </div>
          </div>
        </ClickOut>
      </Detached>
    );
  }
}

export default Container;
