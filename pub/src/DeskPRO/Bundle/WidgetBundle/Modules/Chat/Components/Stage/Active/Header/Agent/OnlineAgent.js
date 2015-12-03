import React, { PropTypes } from 'react';

export class OnlineAgent extends React.Component {

  static propTypes = {
    agentName: PropTypes.string,
    agentAvatar: PropTypes.string,
    departmentName: PropTypes.string
  };

  render() {
    const { agentAvatar, agentName, departmentName } = this.props;
    const style = {};

    if (agentAvatar) {
      style.backgroundImage = `url(${agentAvatar})`;
    }

    return (
      <div className="dpdesignportal-chat-header">
        <div className="dpdesignportal-chat-header-avatar-container">
          <ul>
            <li>
              <div className="dpdesignportal-chat-header-avatar" style={style}>
                <i className="fa fa-user"></i>
              </div>
            </li>
          </ul>
        </div>
        <hr/>
        <h1>You are chatting with <span className="name">{agentName}</span></h1>
        <h2>{departmentName}</h2>
      </div>
    );
  }
}
