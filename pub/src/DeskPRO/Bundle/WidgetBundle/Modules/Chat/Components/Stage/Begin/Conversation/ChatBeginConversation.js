import React from 'react';

export class ChatBeginConversation extends React.Component {

  render() {
    return (
      <div>
        <div className="dpdesignportal-collect-user-info-header">
          <span className="img" />
          <span className="text">Your chat is starting...</span>
        </div>

        <div className="dpdesignportal-collect-user-info">
          <span className="title">Just so we know lorel ipsum, what's your name?</span>
          <form>
            <input type="text" placeholder="First & last name" />
            <input type="submit" value="Go" />
          </form>
        </div>

        <div className="dpdesignportal-collect-user-info">
          <span className="title">What's your email address so we can lorel ipsum?</span>
          <form>
            <input type="text" placeholder="email@example.com" />
            <input type="submit" value="Go" />
            <span className="checkbox-container">
              <span className="user-collect-checkbox "><i className="fa fa-check"></i></span>
              <span className="checkbox-text">I prefer not to say</span>
            </span>
          </form>
        </div>
      </div>
    );
  }
}
