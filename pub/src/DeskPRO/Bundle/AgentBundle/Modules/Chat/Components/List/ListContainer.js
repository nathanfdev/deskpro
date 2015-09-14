import React from 'react';
import { connect } from 'react-redux';
import { List } from './List';

@connect(state => ({
  elements: state.Chat.list.elements,
  viewMode: state.Chat.list.viewMode
}))
export class ListContainer extends React.Component {
  render() {
    return (
      <List {...this.props} />
    );
  }
}
