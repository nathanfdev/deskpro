import PropTypes from 'prop-types';
import React from 'react';
import { portalUrlGenerator } from 'DeskPRO/Bundle/PortalBundle/Http/PortalUrlGenerator';
import map from 'lodash/map';

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
        {map(usersources, us =>
          <div key={us.id}>
            <a
              href={portalUrlGenerator.path(`/login/authenticate/${us.id}?return=${window.location.href}`)}
              className={us.classes.join(' ')}
            >
              {us.icon && <i className={us.icon} />}
              <span>{us.text}</span>
            </a>
          </div>
        )}
      </div>
    );
  }
}
