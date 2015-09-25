import React, { Component } from 'react';
import { connect } from 'react-redux';
import { List } from './List';

@connect(state => state.CrmNav)
export class ListContainer extends Component {
  render() {
    return (
      <List {...this.props} />
    );
  }
}
