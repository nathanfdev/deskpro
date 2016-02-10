import React, { PropTypes } from 'react';
import Loader from 'react-loader';
import { connect } from 'react-redux';
// agents
import { agentsSelector, agentsStatusSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Selectors/agentsSelectors';
import { meSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/RecordStores/Selectors/meSelectors';
// teams
import { myAgentTeamsSelector, myAgentTeamsStatusSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Selectors/agentTeamsSelectors';
import { myDepartmentsSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/RecordsStore';

@connect(state => ({
  me: meSelector(state),
  agents: agentsSelector(state),
  agentsStatus: agentsStatusSelector(state),
  teams: myAgentTeamsSelector(state),
  teamsStatus: myAgentTeamsStatusSelector(state),
  departments: myDepartmentsSelector(state),
  current: state.IM.chats.get('current')
}))
export class Header extends React.Component {
  static propTypes = {
    me: PropTypes.object.isRequired,
    agents: PropTypes.object.isRequired,
    agentsStatus: PropTypes.object.isRequired,
    teams: PropTypes.object.isRequired,
    teamsStatus: PropTypes.object.isRequired,
    departments: PropTypes.object.isRequired,
    current: PropTypes.object.isRequired,
    dispatch: PropTypes.func.isRequired,
    toggleSearch: PropTypes.func.isRequired,
    online: PropTypes.bool.isRequired,
    onClose: PropTypes.func.isRequired
  };

  calculateAgentText = () => {
    const { agents, current, me } = this.props;
    if (agents && agents.size > 0) {
      const filteredAgents = current.agents.filter(agent => agent !== me.get('id') );
      let text = agents.getIn([filteredAgents[0], 'name']);
      if (filteredAgents.length > 1) {
        text = ' and ' + (filteredAgents.length - 1) + ' more';
      }
      return text;
    }
  };

  wrapHeaderText = (text) => {
    return <h1>Your IM with <span>{text}</span> {this.renderOnline()}</h1>;
  };

  renderHeader() {
    const { current, teams, departments } = this.props;
    let text;
    let render;
    switch (current.chat_type) {
      case 'agent':
        text = this.calculateAgentText();
        render = this.wrapHeaderText(text);
        break;
      case 'team':
        text = teams.getIn([current.agent_teams[0], 'name']);
        render = this.wrapHeaderText(text);
        break;
      case 'department':
        text = departments.getIn([current.departments[0], 'title']);
        render = this.wrapHeaderText(text);
        break;
      case 'everyone':
        text = 'everyone';
        render = this.wrapHeaderText(text);
        break;
      default:
        render = <Loader opacity={0} scale={0.5} left="20" components="span" color="#fff" width={3} top="45%"/>;
    }

    return render;
  }

  renderOnline() {
    if (this.props.current.chat_type === 'agent') {
      return this.props.online ? <b className="user-status online"></b> : <b className="user-status offline"></b>;
    }
  }

  render() {
    return (
      <header>
        <div className="header-controls">
          <a href="#" onClick={this.props.toggleSearch}><i className="fa fa-search"></i> Search IM</a>
            <span className="close">
              <a href="#" onClick={this.props.onClose}><i className="fa fa-times"></i></a>
            </span>
        </div>
        { this.renderHeader() }
      </header>
    );
  }

}
