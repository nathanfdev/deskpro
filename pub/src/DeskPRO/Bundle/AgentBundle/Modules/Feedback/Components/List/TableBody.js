import React from 'react';
import {Row} from './Row';

export class TableBody extends React.Component {

  render() {
    const {feedback} = this.props;
    return (
      <tbody>
      {feedback.map((feedback, index) => <Row key={index} feedback={feedback}/>)}
      </tbody>
    );
  }
}