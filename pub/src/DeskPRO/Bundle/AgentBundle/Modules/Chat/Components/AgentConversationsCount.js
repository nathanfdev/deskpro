import React from 'react';
import { connect } from 'redux/react';
import * as actions from '../Actions/chatConversationsNavFrameActions'

@connect(state => ({
  names: state.ChatConversationsNavFrame.agentNames
}))
export default class AgentConversationsCount extends React.Component {

  constructor(props) {
    super(props);
    this.props.dispatch(actions.loadAgentName(this.props.agent));
  }

  render() {
    console.log("rendering AgentConversationsCount");

    const {count, agent} = this.props;

    return (
      <li>
        <div className="list-counter-bucket">
          <a className="list-counter" href="#">{count}</a>
        </div>
        <a href="#" className="item">{this.getName(agent)}</a>
      </li>
    );
  }

  getName(id) {
    return this.props.names[id];
  }
}
