import React from 'react';
import { connect } from 'redux/react';
import * as actions from '../../Actions/chatConversationsNavFrameActions'

@connect(state => ({
  agentNames: state.ChatConversationsNavFrame.agentNames,
}))
export class AgentCountItem extends React.Component {
  render() {
    const {count, group} = this.props;
    const label = this.props.agentNames[group];

    return (
      <li>
        <div className="list-counter-bucket">
          <a className="list-counter active" href="#">{count}</a>
        </div>
        <a href="#" className="item">{label}</a>
      </li>
    );
  }
}
