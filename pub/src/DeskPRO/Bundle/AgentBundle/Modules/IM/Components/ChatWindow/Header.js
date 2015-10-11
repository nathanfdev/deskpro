import React from 'react';

export class Header extends React.Component {
  render() {
    return (
      <header>
        <div className="header-controls">
          <a href="#"><i className="fa fa-search"></i> Search IM</a>
                    <span className="close">
                      <a href="#" onClick={this.props.handleCloseChat}><i className="fa fa-times"></i></a>
                    </span>
        </div>
        { this.renderHeader() }
      </header>
    );
  }

  renderHeader() {
    const { current, teams, departments } = this.props;
    let text;
    switch (current.chat_type) {
      case 'agent':
        text = this.calculateAgentText();
        break;
      case 'team':
        text = teams.getIn([current.agent_teams[0], 'name']);
        break;
      case 'department':
        text = departments.getIn([current.departments[0], 'title']);
        break;
      default:
        text = 'Unknown chat. ALARM!!!';
    }

    return <h1>Your IM with <span>{text}</span> {this.renderOnline()}</h1>
  }

  renderOnline() {
    if(this.props.current.chat_type === 'agent') {
      return <b className="user-status online"></b>
    }
  }

  calculateAgentText = () =>
  {
    const { agents, current, me } = this.props;
    if(agents && agents.size > 0) {
      const filteredAgents = current.agents.filter(agent => agent != me.get('id') );
      let text = agents.getIn([filteredAgents[0], 'name']);
      if(filteredAgents.length > 1) {
        text = ' and ' + (filteredAgents.length - 1) + ' more';
      }
      return text;
    }
  }
}
