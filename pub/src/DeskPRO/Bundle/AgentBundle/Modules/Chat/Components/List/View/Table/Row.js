import React from 'react';

export class Row extends React.Component {

  render() {
    const {element} = this.props;

    return (
      <tr>
        <td className="id-col"><span className="dpw--item-id">#{element.id}</span></td>
        <td></td>
        <td className="agent-col">
          <div className="agent">
            <span className="dpw--avatar-face" style={{backgroundImage: "url(../img/avatars/avatar4.png)"}}></span>
            {element.agent}
          </div>
        </td>
        <td></td>
        <td className="item-title">{element.subject}</td>
        <td></td>
        <td></td>
      </tr>);
  }
}