import PropTypes from 'prop-types';
import React from 'react';
import classNames from 'classnames';
import Isvg from 'react-inlinesvg';
import agentPhrases from 'DeskPRO/Bundle/AgentBundle/AgentPhrases';
import { PopUp } from 'DeskPRO/Component/Semantic/PopUp';
import { List, ListElement } from 'DeskPRO/Component/Semantic/List';
import { Toggle, Range } from 'DeskPRO/Component/Semantic/Form';
import { getDepartmentAgents } from '../../Application/Actions/departmentActions';

class Chat extends React.Component {

  static propTypes = {
    agents:          PropTypes.array,
    chatDepartments: PropTypes.array,
    onlineAgents:    PropTypes.object,
    activeChat:      PropTypes.bool,
    volume:          PropTypes.number,
    updateVolume:    PropTypes.func,
    onToggleChat:    PropTypes.func
  };

  static defaultProps = {
    onlineAgents: [],
    onToggleChat: () => {},
    updateVolume: () => {}
  };

  static getAgentsList(agents, key) {
    return (
      <List key={`list${key}`} className="agents">
        {agents.map((agent) => {
          let img = agent.get('avatar').get('default_url_pattern');
          if (agent.get('avatar').get('url_pattern')) {
            img = agent.get('avatar').get('url_pattern');
          }
          img = img.replace(/\{\{IMG_SIZE}}/, 15);
          return <ListElement key={`agent${String(agent.get('id'))}`} label={agent.get('name')} image={img} />;
        })}
      </List>
    );
  }

  constructor(props) {
    super(props);
    this.state = {
      volume:         this.props.volume || 8,
      departmentMode: false,
      previousVolume: 8
    };
  }

  getStatus() {
    const { activeChat, onlineAgents } = this.props;
    const status = activeChat ? agentPhrases.get('agent.general.on') : agentPhrases.get('agent.general.off');

    return (
      <div className="status">
        {status} <span className="count">({onlineAgents.size})</span>
      </div>
    );
  }

  getAgents() {
    const { agents, onlineAgents, chatDepartments } = this.props;
    const { departmentMode } = this.state;

    let result = [];
    if (departmentMode) {
      chatDepartments.forEach((department, index) => {
        const departmentAgents = getDepartmentAgents(department).filter(agent =>
          onlineAgents.contains(agent.get('id'))
        );

        if (departmentAgents.size) {
          result.push(<h4 key={`dep${index}`}>{department.get('title')}</h4>);
          result.push(<hr key={`hr${index}`} />);
          result.push(Chat.getAgentsList(departmentAgents, department.get('id')));
        }
      });
    } else {
      result = Chat.getAgentsList(agents.filter(agent => onlineAgents.contains(agent.get('id'))), 0);
    }

    return result;
  }

  getPopupContent() {
    const { chatDepartments, activeChat, onlineAgents, onToggleChat } = this.props;
    const { departmentMode } = this.state;
    const volume = parseInt(this.state.volume, 10);

    return (
      <div id="chat-menu">
        <div className="header">
          {agentPhrases.get('agent.general.chat')}&nbsp;
          <span className="count">
            ({agentPhrases.get('agent.tickets.count_agents', { count: onlineAgents.size })})
          </span>
        </div>
        <div className="description">
          <Toggle active={activeChat} onChange={onToggleChat} className="small">
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
          {agentPhrases.get('agent.tickets.count_agents', { count: onlineAgents.size })}
          {chatDepartments && chatDepartments.length > 1 &&
            <button
              className={classNames('ui button basic tiny compact right department-filter', { active: departmentMode })}
              onClick={this.toggleDepartmentMode}
            >
              <i className="icon users" />
              {agentPhrases.get('agent.chat.by_department')}
            </button>}
          {this.getAgents()}
        </div>
      </div>
    );
  }

  toggleDepartmentMode = () => {
    this.setState({
      departmentMode: !this.state.departmentMode
    });
  };

  togglePopup = () => {
    this.chatPopup.togglePopup();
  };

  toggleVolume = () => {
    const { updateVolume } = this.props;
    const { volume, previousVolume } = this.state;

    if (volume === 0) {
      this.updateAudioVolume(previousVolume);
    } else {
      updateVolume(0);
      this.setState({
        volume:         0,
        previousVolume: volume
      });
    }
  };

  updateAudioVolume = (newVal) => {
    const { updateVolume } = this.props;
    const volume = parseInt(newVal, 10);

    updateVolume(volume);
    this.setState({ volume });
  };

  render() {
    const { activeChat, onlineAgents } = this.props;
    if (!window.DESKPRO_APP_SETTINGS['core.apps_chat'] || !window.DESKPRO_PERSON_PERMS['agent_chat.use']) {
      return null;
    }

    return (
      <div className="chat" onClick={this.togglePopup}>
        <PopUp
          positionMy="right top"
          positionAt="right bottom"
          elementId="chat-menu-popup"
          zIndex={99999}
          autoOpen={false}
          ref={(c) => { this.chatPopup = c; }}
          className="chat_popup"
          content={this.getPopupContent()}
        >
          <Isvg
            className={classNames({ on: activeChat, others: onlineAgents.size })}
            src={`${window.DESKPRO_APP_ASSETS_URL}/DeskPRO/Bundle/AgentBundle/Resources/img/topbar/chat.svg`}
          />
          <br />
          {this.getStatus()}
        </PopUp>
      </div>
    );
  }
}

export default Chat;
