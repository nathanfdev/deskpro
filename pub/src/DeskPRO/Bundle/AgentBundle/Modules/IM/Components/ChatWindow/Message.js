import React from 'react';

export default class Message extends React.Component {
  render() {
    const style = {
      backgroundImage: 'url("' + this.props.message.author.gravatar_url + '")'
    };
    return (
      <li className="chat-message old">
        <a href="#" className="chat-avatar" style={style}></a>
        <span className="time">4.13pm <i className="fa fa-clock-o"></i></span>

        <p>{this.props.message.text}</p>
      </li>
    );
  }
}