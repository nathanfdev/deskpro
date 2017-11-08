import PropTypes from 'prop-types';
import React from 'react';
import { AgentsList } from './Agents/AgentsList';
import { TeamsList } from './Teams/TeamsList';
import { DepartmentsList } from './Departments/DepartmentsList';
import * as chatsActions from '../Actions/chatsActions';
import { ClickOut } from 'DeskPRO/Component/ClickOut';

export class IMOverlay extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired
  };

  onClickOut = () => {
    this.props.dispatch(chatsActions.toggleOverlay());
  };

  onStartChat = event => {
    event.preventDefault();
    this.props.dispatch(chatsActions.startChat('0', 'everyone'));
  };

  render() {
    return (
      <ClickOut onClickOut={this.onClickOut}>
        <div className="dropdown im-dropdown" id="im-dropdown">
          <header className="dropdown-header">Agent Instant Messages</header>
          <div className="wrapper">
            <AgentsList />

            <div className="bucket right">
              <a href="#" onClick={this.onStartChat} className="broadcast-to-all">
                <i className="fa fa-bullhorn"></i> Broadcast to Everyone
              </a>
              <div className="im-list-wrapper">
                <h2>Teams</h2>
                <TeamsList />
                <h2>Departments</h2>
                <DepartmentsList />
              </div>
            </div>
          </div>
        </div>
      </ClickOut>
    );
  }
}
