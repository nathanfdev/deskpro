import React, { PropTypes } from 'react';
import { PopUp } from 'DeskPRO/Component/Semantic/PopUp';
import { List, ListElement } from 'DeskPRO/Component/Semantic/List';
import { Toggle, Range } from 'DeskPRO/Component/Semantic/Form';

class Chat extends React.Component {
  static propTypes = {
    onlineAgents: PropTypes.arrayOf(
      PropTypes.shape({
        name:       PropTypes.string.isRequired,
        pictureSrc: PropTypes.string,
        department: PropTypes.string
      })
    )
  };
  constructor(props) {
    super(props);
    this.state = {
      activeChat: false,
      volume:     5
    };
    this.toggleChat = this.toggleChat.bind(this);
    this.updateAudioVolume = this.updateAudioVolume.bind(this);
  }

  getAgents() {
    const departments = this.sortAgentsByDepartment();
    const result = [];
    Object.keys(departments).map((key) => {
      result.push(<h4>{departments[key].label}</h4>);
      result.push(<hr />);
      let agentList = [];
      for (const agent of departments[key].agents) {
        result.push(<ListElement label={agent.name} />);
      }
      result.push(<List>{agentList}</List>);
      return true;
    });
    return result;
  }

  getPopupContent() {
    const { activeChat, volume } = this.state;
    const { onlineAgents } = this.props;
    return (<div id="chat-menu">
      <div className="header"><strong>Chat</strong>
        &nbsp;({onlineAgents.length} Agents, 0 Users)
      </div>
      <div className="description">
        <Toggle active={activeChat} onChange={this.toggleChat}>
          Online for chat
        </Toggle>
        <hr />
        <i className="icon volume up" /> Notification volume<br />
        <Range min={0} max={10} value={volume} onChange={this.updateAudioVolume} />
        <hr />
        {onlineAgents.length} online agents
        {this.getAgents()}
      </div>
    </div>);
  }

  sortAgentsByDepartment() {
    const departments = {};
    let key = 0;
    for (const agent of this.props.onlineAgents) {
      if (!departments.hasOwnProperty(agent.department)) {
        departments[agent.department] = {
          label:  agent.department,
          key:    key++,
          agents: [
            agent
          ]
        };
      } else {
        departments[agent.department].agents.push(agent);
      }
    }
    return departments;
  }

  updateAudioVolume(newVal) {
    this.setState({
      volume: newVal
    });
  }

  toggleChat(newVal) {
    this.setState({
      activeChat: newVal
    });
  }

  render() {
    return (<div className="chat">
      <PopUp
        positionMy="right top"
        positionAt="right bottom"
        id={2}
        elementId="chat-menu-popup"
        zIndex={99999}
        content={this.getPopupContent()}
      >
        <i className="icon talk" />
      </PopUp>
    </div>);
  }
}
export default Chat;
