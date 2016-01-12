import React, { PropTypes } from 'react';
import { Header } from '../Begin/Header';

export class ChatValidation extends React.Component {

  static propTypes = {
    children: PropTypes.any
  };

  render() {
    return (
      <div>
        <Header />
        {this.props.children}
      </div>
    );
  }
}
