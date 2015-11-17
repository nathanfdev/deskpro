import React from 'react';

export class Row extends React.Component {

  render() {
    const {element} = this.props;

    return (
      <tr>
        <td className="id-col"><span className="dpw--item-id">#{element.id}</span></td>
      </tr>);
  }
}