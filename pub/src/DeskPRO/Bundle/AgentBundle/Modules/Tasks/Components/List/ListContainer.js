import React from 'react';
import { connect } from 'react-redux';
import { List } from './List';

@connect()
export class ListContainer extends React.Component {

  render() {
    return (
      <List {...this.props} />
    );
  }
}
