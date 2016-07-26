import React, { PropTypes } from 'react';

export class Hello extends React.Component {
  static propTypes = {
    person: PropTypes.object.isRequired
  };

  render() {
    return <span>Hello {this.props.person.fname}  {this.props.person.lname}</span>
  }
}