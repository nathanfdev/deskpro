import React from "react"
import LoginForm from "./LoginForm.js"
import LoginUsersources from "./LoginUsersources.js"

export default class LoginPanel extends React.Component {
  render() {
    return (
      <div>
        <LoginForm />
        <LoginUsersources usersources={this.props.usersources} />
      </div>
    );
  }
}
