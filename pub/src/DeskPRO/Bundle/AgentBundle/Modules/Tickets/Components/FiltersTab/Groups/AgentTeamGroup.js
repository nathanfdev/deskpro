import React from "react";

import { connect } from "react-redux";
import * as AgentTeamActions from "../../../Actions/AgentTeamActions";

@connect(state => ({
  AgentTeams: state.Tickets.AgentTeams,
}))
export default class AgentTeamGroup extends React.Component {
  constructor(props) {
    super(props);
    const { dispatch, AgentTeams } = this.props;
    this.agent_team_id = this.props.item;

    if(this.agent_team_id && typeof(AgentTeams[this.agent_team_id]) === 'undefined') {
      dispatch(AgentTeamActions.loadAgentTeam(this.agent_team_id));
    }
  }

  render() {
    const { AgentTeams, count, item } = this.props;

    if(!AgentTeams[this.agent_team_id]) {
      return (<span></span>);
    } else {
      const key = "agent-" + this.agent_team_id;
      return (
        <li key={key}>
          <div className="list-counter-bucket"><a href="#" className="list-counter">{count}</a></div>
          <a href="#" className="item">{AgentTeams[this.agent_team_id].name}</a>
        </li>
      );
    }
  }
}
