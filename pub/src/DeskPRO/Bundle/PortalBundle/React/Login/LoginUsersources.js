import React, { PropTypes } from 'react';
import { portalUrlGenerator } from 'DeskPRO/Bundle/PortalBundle/Http/PortalUrlGenerator';
import _ from 'lodash';

export class LoginUsersources extends React.Component {

  static propTypes = {
    usersources: PropTypes.array
  };

  render() {
    const { usersources } = this.props;
    if (usersources.length === 0) {
      return null;
    }

    return (
      <div>
        {_.map(usersources, (us) =>
          <div key={us.id}>
            <a
              href={portalUrlGenerator.path('/login/authenticate/' + us.id)}
              className={us.classes.join(' ')}>
              { us.icon ? (<i className={us.icon}></i>) : null }
              <span> {us.text}</span>
            </a>
          </div>
        )}
      </div>
    );
  }
}
