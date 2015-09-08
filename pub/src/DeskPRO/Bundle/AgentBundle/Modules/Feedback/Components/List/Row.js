import React from 'react';

export class Row extends React.Component {

  render() {
    const {feedback} = this.props;

    return (
      <tr key={feedback.id} className="single-row">
        <td className="id-col"><span className="dpw--item-id">#{feedback.id}</span></td>
        <td>{feedback.num_ratings}</td>
        <td className="item-title"><a href="#">{feedback.title}</a></td>
        <td>{feedback.status}</td>
        <td>{feedback.type}</td>
        <td></td>
        <td>
          <div className="user">
            <span className="dpw--avatar-face" style={{backgroundImage: "url(../img/avatars/avatar1.png)"}}></span>
            <span className="agent-name">{feedback.author_name}</span>
          </div>
        </td>
      </tr>);
  }
}