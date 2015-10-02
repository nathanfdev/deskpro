import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { HeaderWidget } from '../../IM/Components/HeaderWidget';

@connect(state => ({
  user: state.user
}))
export class Header extends React.Component {
  static propTypes = {
    user: PropTypes.object.isRequired
  };

  render() {
    const { user } = this.props;

    return (<header className="dp-window-header top-bar">
      <a href="https://www.deskpro.com/" className="logo"></a>
      <HeaderWidget/>

      <div className="user-options">
        <a href="#" className="notification-button">
          <span className="title"><i className="fa fa-dollar"></i></span>
        </a>

        <a href="#" className="notification-button">
          <span className="notification-count">23</span>
          <span className="title"><i className="fa fa-cog"></i> Admin</span>
        </a>

        <a href="#" className="user-options-button">
          <span className="user-photo" style={{backgroundImage: 'url(' + user.picture_url + ')'}}></span>
          Settings
          <i className="fa fa-angle-down"></i>
        </a>
      </div>

    </header>);
  }
}
