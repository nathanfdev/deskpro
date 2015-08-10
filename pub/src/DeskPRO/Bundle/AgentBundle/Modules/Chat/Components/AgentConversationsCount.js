import React from 'react';
import * as actions from '../Actions/chatConversationsNavFrameActions'

export default class AgentConversationsCount extends React.Component {

  render() {
    const {count, agent, label} = this.props;

    return (
      <li>
        <div className="list-counter-bucket">
          <a className="list-counter" href="#">{count}</a>
        </div>
        <a href="#" className="item">{label}</a>
      </li>
    );
  }
}
