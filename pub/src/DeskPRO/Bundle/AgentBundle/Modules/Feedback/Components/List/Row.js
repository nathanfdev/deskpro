import React from 'react';
import { connect } from 'redux/react';

export class Row extends React.Component {

  render() {
    const {feedback} = this.props;

    return (
      <tr key={feedback.id}>
        <td>{feedback.id}</td>
        <td>{feedback.num_ratings}</td>
        <td><a href="#">{feedback.title}</a></td>
        <td>{feedback.status}</td>
        <td>{feedback.type}</td>
        <td></td>
        <td>{feedback.author_name}</td>
      </tr>);
  }
}