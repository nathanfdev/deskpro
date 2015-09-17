import React from 'react';
import { connect } from 'react-redux';
import { List } from '../../Components/List/List';

@connect(state => ({
  elements: state.Chat.list.get('elements'),
  viewMode: state.Chat.list.get('viewMode')
}))
export class ListContainer extends React.Component {
  render() {
    return (
      <List {...this.props} />
    );
  }
}
