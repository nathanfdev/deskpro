import React, { PropTypes } from 'react';
import classNames from 'classnames';
import Isvg from 'react-inlinesvg';
import agentPhrases from 'DeskPRO/Bundle/AgentBundle/AgentPhrases';
import { PopUp } from 'DeskPRO/Component/Semantic/PopUp';
import { List, ListElement } from 'DeskPRO/Component/Semantic/List';
import { Toggle, Range } from 'DeskPRO/Component/Semantic/Form';

class Chat extends React.Component {
  static propTypes = {
    agents:          PropTypes.array,
    chatDepartments: PropTypes.array,
    onlineAgents:    PropTypes.array,
    volume:          PropTypes.number,
    me:              PropTypes.object,
    updateVolume:    PropTypes.func
  };

  static defaultProps = {
    onlineAgents: [],
    onToggleChat() {},
    updateVolume() {}
  };

  static getAgentsList(agents, key) {
    return (<List key={`list${key}`} className="agents">
      {
        agents.map((agent) => {
          let img = agent.get('avatar').get('default_url_pattern');
          if (agent.get('avatar').get('url_pattern')) {
            img = agent.get('avatar').get('url_pattern');
          }
          img = img.replace(/\{\{IMG_SIZE}}/, 15);
          return <ListElement key={`agent${String(agent.get('id'))}`} label={agent.get('name')} image={img} />;
        })
      }
    </List>);
  }

  constructor(props) {
    super(props);
    this.state = {
      onlineAgents:   this.props.onlineAgents,
      activeChat:     false,
      volume:         this.props.volume || 8,
      departmentMode: false,
      previousVolume: 8
    };
  }

  componentDidMount() {
    const self = this;
    if (window.DeskPRO_Window) {
      window.DeskPRO_Window.getMessageBroker().addMessageListener('agent.online-agents-userchat', (info) => {
        self.setState({
          onlineAgents: info.online_agents
        });
        self.refreshOnlineAgentsList();
      });
    }
  }

  getStatus() {
    const { activeChat, onlineAgents } = this.state;
    const status = activeChat ? agentPhrases.get('agent.general.on') : agentPhrases.get('agent.general.off');
    return (<div className="status">
      {status} <span className="count">({onlineAgents.length})</span>
    </div>);
  }

  getAgents() {
    const onlineAgents = this.props.agents.filter(agent =>
      this.state.onlineAgents.indexOf(String(agent.get('id'))) !== -1
    );
    let result = [];
    if (this.state.departmentMode) {
      this.props.chatDepartments.map((department, index) => {
        const agents = [];
        onlineAgents.map((agent) => {
          if (department.has('agents') && department.get('agents').toArray().indexOf(agent.get('id')) !== -1) {
            agents.push(agent);
          }
          return true;
        });
        if (agents.length) {
          result.push(<h4 key={`dep${index}`}>{department.get('title')}</h4>);
          result.push(<hr key={`hr${index}`} />);
          result.push(Chat.getAgentsList(agents, department.get('id')));
        }
        return true;
      });
    } else {
      result = Chat.getAgentsList(onlineAgents, 0);
    }
    return result;
  }

  getPopupContent() {
    const { activeChat, onlineAgents, departmentMode } = this.state;
    const volume = parseInt(this.state.volume, 10);
    return (<div id="chat-menu">
      <div className="header">
        {agentPhrases.get('agent.general.chat')}&nbsp;
        <span className="count">
          ({agentPhrases.get('agent.tickets.count_agents', { count: onlineAgents.length })})
        </span>
      </div>
      <div className="description">
        <Toggle active={activeChat} onChange={this.toggleChat} className="small">
          {agentPhrases.get('agent.chat.online_for_chat')}
        </Toggle>
        <hr className="full" />
        <i
          onClick={this.toggleVolume}
          className={classNames(
            'icon',
            'volume',
            { off: volume === 0, up: volume > 7, down: (volume <= 7 && volume > 0) }
          )}
        /> {agentPhrases.get('agent.chat.notification_volume')}<br />
        <Range min={0} max={10} value={volume} onChange={this.updateAudioVolume} />
        <hr className="full" />
        {agentPhrases.get('agent.tickets.count_agents', { count: onlineAgents.length })}
        <button
          className={classNames('ui', 'button', 'basic', 'tiny', 'compact', 'right', 'department-filter',
            { active: departmentMode })}
          onClick={this.toggleDepartmentMode}
        >
          <i className="icon users" />
          {agentPhrases.get('agent.chat.by_department')}
        </button>
        {this.getAgents()}
      </div>
    </div>);
  }

  refreshOnlineAgentsList = () => {
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
  };

  toggleDepartmentMode = () => {
    this.setState({
      departmentMode: !this.state.departmentMode
    });
  };

  togglePopup = () => {
    this.chatPopup.togglePopup();
  };

  toggleVolume = () => {
    if (this.state.volume === 0) {
      this.updateAudioVolume(this.state.previousVolume);
    } else {
      this.setState({
        previousVolume: this.state.volume,
      });
      this.updateAudioVolume(0);
    }
  };

  updateAudioVolume = (newVal) => {
    const volume = parseInt(newVal, 10);
    this.props.updateVolume(volume);
    this.setState({
      volume
    });
  };

  toggleChat = (status) => {
    const postData = [];
    const url = `${window.BASE_URL}agent/misc/set-agent-status/available`;
    const onlineAgents = this.state.onlineAgents;

    if (status) {
      onlineAgents.push(`${this.props.me.get('id')}`);
      postData.push({
        name:  'is_chat_available',
        value: 1
      });
    } else {
      onlineAgents.splice(onlineAgents.indexOf(`${this.props.me.get('id')}`), 1);
      postData.push({
        name:  'is_chat_available',
        value: 0
      });
    }
    this.setState({
      activeChat: status,
      onlineAgents
    });

    const self = this;
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
  };

  render() {
    const { activeChat, onlineAgents } = this.state;
    return (<div className="chat" onClick={this.togglePopup}>
      <PopUp
        positionMy="right top"
        positionAt="right bottom"
        id={2}
        elementId="chat-menu-popup"
        zIndex={99999}
        autoOpen={false}
        ref={(c) => { this.chatPopup = c; }}
        className="chat_popup"
        content={this.getPopupContent()}
      >
        <Isvg
          className={classNames({ on: activeChat, others: onlineAgents.length })}
          src={`${window.DESKPRO_APP_ASSETS_URL}/DeskPRO/Bundle/AgentBundle/Resources/img/topbar/chat.svg`}
        />
        <br />
        {this.getStatus()}
      </PopUp>
    </div>);
  }
}
export default Chat;
