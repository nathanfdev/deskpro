import React from 'react';
import { List } from './List';
import { connect } from 'redux/react';

@connect(state => ({
  elements: state.ChatList.elements,
  viewModeOptions: state.ChatList.viewModeOptions
}))
export class ListContainer extends React.Component {
  render() {
    return (
      <List {...this.props} />
    );
  }
}
