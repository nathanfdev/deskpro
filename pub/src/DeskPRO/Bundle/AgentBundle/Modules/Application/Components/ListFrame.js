import React, { PropTypes } from 'react';

export default class ListFrame extends React.Component {
  static propTypes = {
    children: PropTypes.object.isRequired
  }

  render() {
    return (<section className="dp-list-frame">
      {this.props.children}
    </section>);
  }
}
