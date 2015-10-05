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
    "use strict";
    const { agents, current, me } = this.props;
    const filteredAgents = current.agents.filter(agent => agent != me.id );
    let text = agents.toJS()[filteredAgents[0]].name;
    if(filteredAgents.length > 1) {
      text = ' and ' + (filteredAgents.length - 1) + ' more';
    }
    return <h1>Your IM with <span>{text}</span><b className="user-status online"></b></h1>
  }

}