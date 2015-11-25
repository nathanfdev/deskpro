import React from 'react';
import { Header } from './Header';
import { UserInfoForm } from './UserInfoForm';

export class ChatBeginConversation extends React.Component {

  render() {
    return (
      <div>
        <Header />

        <UserInfoForm title="Just so we know lorel ipsum, what's your name?">
          <input type="text" placeholder="First & last name" />
          <input type="submit" value="Go" />
        </UserInfoForm>

        <UserInfoForm title="What's your email address so we can lorel ipsum?">
          <input type="text" placeholder="email@example.com" />
          <input type="submit" value="Go" />
          <span className="checkbox-container">
            <span className="user-collect-checkbox "><i className="fa fa-check"></i></span>
            <span className="checkbox-text">I prefer not to say</span>
          </span>
        </UserInfoForm>
      </div>
    );
  }
}
