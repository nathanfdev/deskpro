import React, { PropTypes } from 'react';
import { connect } from 'redux/react';
import IMButton from '../../IM/Components/IMButton';

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
      <IMButton/>
    </header>);
  }
}
