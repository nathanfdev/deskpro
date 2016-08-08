import React, { PropTypes } from 'react';
import classNames from 'classnames';
import { PopUp } from 'DeskPRO/Component/Semantic/PopUp';
import { List, ListElement } from 'DeskPRO/Component/Semantic/List';
import { Toggle, Range } from 'DeskPRO/Component/Semantic/Form';

class Chat extends React.Component {
  static propTypes = {
    agents:       PropTypes.array,
    onlineAgents: PropTypes.array,
    volume:       PropTypes.number,
    onToggleChat: PropTypes.func,
    updateVolume: PropTypes.func
  };

  static defaultProps = {
    onlineAgents: [],
    onToggleChat() {},
    updateVolume() {}
  };

  constructor(props) {
    super(props);
    this.state = {
      onlineAgents:   this.props.onlineAgents,
      activeChat:     false,
      volume:         this.props.volume || 8,
      previousVolume: 8
    };
    this.toggleChat = this.toggleChat.bind(this);
    this.toggleVolume = this.toggleVolume.bind(this);
    this.updateAudioVolume = this.updateAudioVolume.bind(this);
    this.refreshOnlineAgentsList = this.refreshOnlineAgentsList.bind(this);
  }

  componentDidMount() {
    const self = this;
    if (window.DeskPRO_Window) {
      window.DeskPRO_Window.getMessageBroker().addMessageListener('agent.online-agents-userchat', info => {
        self.setState({
          onlineAgents: info.online_agents
        });
        self.refreshOnlineAgentsList();
      });
    }
  }

  getStatus() {
    const { activeChat, onlineAgents } = this.state;
    const status = activeChat ? 'ON' : 'OFF';
    return (<div className="status">
      {status} <span className="count">({onlineAgents.length})</span>
    </div>);
  }

  getAgents() {
    const departments = this.sortAgentsByDepartment();
    const result = [];
    Object.keys(departments).map((key) => {
      result.push(<h4 key={`dep${key}`}>{departments[key].label}</h4>);
      result.push(<hr key={`hr${key}`} />);
      let agentList = [];
      for (const agent of departments[key].agents) {
        let img = agent.avatar.default_url_pattern;
        if (agent.avatar.url_pattern) {
          img = agent.avatar.url_pattern;
        }
        img = img.replace(/\{\{IMG_SIZE}}/, 15);
        agentList.push(<ListElement key={`agent${agent.id}`} label={agent.name} image={img} />);
      }
      result.push(<List key={`agents${key}`} classes={['agents']}>{agentList}</List>);
      return true;
    });
    return result;
  }

  getPopupContent() {
    const { activeChat, onlineAgents } = this.state;
    const volume = parseInt(this.state.volume, 10);
    return (<div id="chat-menu">
      <div className="header">
        Chat <span className="count">({onlineAgents.length} Agents)</span>
      </div>
      <div className="description">
        <Toggle active={activeChat} onChange={this.toggleChat}>
          Online for chat
        </Toggle>
        <hr />
        <i
          onClick={this.toggleVolume}
          className={classNames(
            'icon',
            'volume',
            { off: volume === 0, up: volume > 7, down: (volume <= 7 && volume > 0) }
          )}
        /> Notification volume {volume}<br />
        <Range min={0} max={10} value={volume} onChange={this.updateAudioVolume} />
        <hr />
        {onlineAgents.length} online agents
        {this.getAgents()}
      </div>
    </div>);
  }

  refreshOnlineAgentsList() {
    const { onlineAgents } = this.state;
    let hasMe = false;
    for (const agentId of onlineAgents) {
      if (parseInt(agentId, 10) === window.DESKPRO_PERSON_ID) {
        hasMe = true;
      }
    }
    this.setState({
      activeChat: hasMe
    });
  }

  sortAgentsByDepartment() {
    const departments = {};
    let key = 0;
    for (const agent of this.props.agents) {
      agent.key = String(agent.id);
      if (this.state.onlineAgents.indexOf(agent.key) === -1) {
        continue;
      }
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

  toggleVolume() {
    if (this.state.volume === 0) {
      this.updateAudioVolume(this.state.previousVolume);
    } else {
      this.setState({
        previousVolume: this.state.volume,
      });
      this.updateAudioVolume(0);
    }
  }

  updateAudioVolume(newVal) {
    const volume = parseInt(newVal, 10);
    this.props.updateVolume(volume);
    this.setState({
      volume
    });
  }

  toggleChat(status) {
    const postData = [];
    const url = `${window.BASE_URL}agent/misc/set-agent-status/available`;
    const self = this;
    if (status) {
      postData.push({
        name:  'is_chat_available',
        value: 1
      });
    } else {
      postData.push({
        name:  'is_chat_available',
        value: 0
      });
    }

    window.$.ajax({
      url,
      type: 'POST',
      data: postData,
      complete() {
        self.setState({
          activeChat: status
        });
        if (self.props.onToggleChat) {
          self.props.onToggleChat();
        }
      }
    });
  }

  render() {
    const { activeChat } = this.state;
    return (<div className="chat">
      <PopUp
        positionMy="right top"
        positionAt="right bottom"
        id={2}
        elementId="chat-menu-popup"
        zIndex={99999}
        opened
        content={this.getPopupContent()}
      >
        <i className={classNames('icon', 'comments', 'outline', { on: activeChat })} /><br />
        {this.getStatus()}
      </PopUp>
    </div>);
  }
}
export default Chat;
