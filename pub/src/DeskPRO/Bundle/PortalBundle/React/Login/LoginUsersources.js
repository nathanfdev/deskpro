import React from "react"
import _ from "lodash"
import PortalUrlGenerator from "DeskPRO/Bundle/PortalBundle/Http/PortalUrlGenerator";

export default class LoginUsersources extends React.Component {
  render() {
    const usersources = this.props.usersources;

    if (usersources.length === 0) {
      return null;
    }

    return (
      <div>
        {_.map(usersources, (us) => {
          return (
            <a
              key={us}
              href={PortalUrlGenerator.path('/login/authenticate/' + us.id)}
              className={us.classes.join(' ')}>
              { us.icon ? (<i className={us.icon}></i>) : null }
              <span> {us.text}</span>
            </a>
          );
        })}
      </div>
    );
  }
}
