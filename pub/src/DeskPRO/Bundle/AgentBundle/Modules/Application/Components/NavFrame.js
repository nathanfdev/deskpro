import React, { PropTypes } from 'react';

export default class NavFrame extends React.Component {
  static propTypes = {
    activeAppId: PropTypes.string.isRequired,
    children: PropTypes.func.isRequired
  }

  render() {
    return (<section className="dp-nav-frame">
      {this.props.children}
    </section>);
  }
}
