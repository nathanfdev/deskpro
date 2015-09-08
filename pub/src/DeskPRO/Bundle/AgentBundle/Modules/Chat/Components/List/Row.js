import React from 'react';

export class Row extends React.Component {

  render() {
    const {element} = this.props;

    return (
      <tr key={element.id}>
        <td>{element.id}</td>
        <td></td>
        <td>{element.agent}</td>
        <td></td>
        <td>{element.subject}</td>
        <td></td>
        <td></td>
      </tr>);
  }
}