import React from "react";

import { connect } from "redux/react";
import * as AgentTeamActions from "../../../Actions/AgentTeamActions";

@connect(state => ({
  agent_teams: state.agent_teams,
}))
export default class AgentTeamGroup extends React.Component {
  constructor(props) {
    super(props);
    const { dispatch, agent_teams } = this.props;
    this.agent_team_id = this.props.item;

    console.log(agent_teams);

    if(this.agent_team_id && typeof(agent_teams[this.agent_team_id]) === 'undefined') {
      dispatch(AgentTeamActions.loadAgentTeam(this.agent_team_id));
    }
  }
  
  render() {
    const { agent_teams, count, item } = this.props;
    
    console.log(agent_teams);
    
    if(!agent_teams[this.agent_team_id]) {
      return (<span></span>);
    } else {
      const key = "agent-" + this.agent_team_id;
      return (
        <li key={key}>
          <div className="list-counter-bucket"><a href="#" className="list-counter">{count}</a></div>
          <a href="#" className="item">{agent_teams[this.agent_team_id].name}</a>
        </li>
      );
    }
  }
}
