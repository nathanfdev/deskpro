import React, { PropTypes } from 'react';

export default class Header extends React.Component {

	static propTypes = {
    user: PropTypes.object
  }

  constructor (props) {
    super(props);

    if (!this.props.user || this.props.user.person_id === 0) {
    	this.props.loadUser();
    }
  }

  render() {
  	const user = this.props.user;
    return (<header className="dp-window-header top-bar">
	      <a href="https://www.deskpro.com/" className="logo"></a>
	      <span>Welcome, {user.display_name}</span>
	    </header>);
  }
}
