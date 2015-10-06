import React from 'react';

export class Message extends React.Component {
  render() {
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
  }
}