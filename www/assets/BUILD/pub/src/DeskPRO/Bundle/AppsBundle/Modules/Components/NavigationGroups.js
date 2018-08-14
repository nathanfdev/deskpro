import React from 'react'; // eslint-disable-line no-unused-vars
import PropTypes from 'prop-types';

export class NavigationGroups extends React.PureComponent {
  static propTypes = {
    notificationsCount: PropTypes.number,
    className:          PropTypes.string.className
  };

  render()  {
    return (
      <div className={this.props.className} >

        <button className="dp-ButtonTabs is-classic is-selected">
          <span className="dp-IconHamburger" />
          { !!this.props.notificationsCount && <span className="dp-IconBadge">{this.props.notificationsCount}</span> }
        </button>

      </div>
    );
  }
}
