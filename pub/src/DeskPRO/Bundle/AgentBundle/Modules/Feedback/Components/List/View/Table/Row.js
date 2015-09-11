import React from 'react';

export class Row extends React.Component {

  render() {
    const {element} = this.props;

    return (
      <tr key={element.id} className="single-row">
        <td className="id-col"><span className="dpw--item-id">#{element.id}</span></td>
        <td>{element.num_ratings}</td>
        <td className="item-title"><a href="#">{element.title}</a></td>
        <td>{element.status}</td>
        <td>{element.type}</td>
        <td></td>
        <td>
          <div className="user">
            <span className="dpw--avatar-face" style={{backgroundImage: "url(../img/avatars/avatar1.png)"}}></span>
            <span className="agent-name">{element.author_name}</span>
          </div>
        </td>
      </tr>);
  }
}