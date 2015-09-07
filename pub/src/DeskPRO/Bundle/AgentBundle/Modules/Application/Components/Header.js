import React, { PropTypes } from 'react';
import { connect } from 'react-redux';

@connect(state => ({
  user: state.user
}))
export default class Header extends React.Component {
	static propTypes = {
    user: PropTypes.object.isRequired
  }

  render() {
  	const { user } = this.props;
    return (<header className="dp-window-header top-bar">
	      <a href="https://www.deskpro.com/" className="logo"></a>
	      <span>Welcome, {user.display_name}</span>
	    </header>);
  }
}
