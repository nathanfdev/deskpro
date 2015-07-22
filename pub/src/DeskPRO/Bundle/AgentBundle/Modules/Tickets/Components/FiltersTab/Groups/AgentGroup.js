import React from "react";

import { connect } from "redux/react";
import * as AgentsActions from "../../../Actions/AgentsActions";

@connect(state => ({
  agents: state.agents,
}))
export default class AgentGroup extends React.Component {
  constructor(props) {
    super(props);
    const { dispatch, agents } = this.props;
    this.agent_id = this.props.item;

    if(this.agent_id && typeof(agents[this.agent_id]) === 'undefined') {
      dispatch(AgentsActions.loadAgent(this.agent_id));
    }
  }
  
  render() {
    const { agents, count, item } = this.props;
    
    if(!agents[this.agent_id]) {
      return (<span></span>);
    } else {
      const key = "agent-" + this.agent_id;
      return (
        <li key={key}>
          <div className="list-counter-bucket"><a href="#" className="list-counter">{count}</a></div>
          <a href="#" className="item">{agents[this.agent_id].name}</a>
        </li>
      );
    }
  }
}
