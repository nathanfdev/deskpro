import React, { PropTypes } from 'react';

export class Message extends React.Component {

  static propTypes = {
    me: PropTypes.object.isRequired,
    agents: PropTypes.object.isRequired,
    message: PropTypes.object.isRequired
  };

  renderMy = () => {
    return (
      <li className="chat-message yours old">
        <div className="message-read-mark"><i className="fa fa-check"></i><i className="fa fa-check"></i></div>
        <span className="time">4.13pm <i className="fa fa-clock-o"></i></span>
        <p>{this.props.message.message}</p>
      </li>
    );
  };

  renderNotMy = () => {
    const author = this.props.agents.get(this.props.message.person_id);
    const style = {
      backgroundImage: 'url("' + author.get('gravatar_url') + '")'
    };
    return (
      <li className="chat-message old">
        <a href="#" title={this.props.message.person_name} className="chat-avatar" style={style}></a>
        <span className="time">4.13pm <i className="fa fa-clock-o"></i></span>
        <p>{this.props.message.message}</p>
      </li>
    );
  };

  render() {
    return (this.props.message.person_id === this.props.me.get('id')) ? this.renderMy() : this.renderNotMy();
  }
}