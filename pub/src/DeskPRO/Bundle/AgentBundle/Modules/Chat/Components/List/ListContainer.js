import React from 'react';
import { connect } from 'react-redux';
import { elementsSelector, viewDataSelector } from '../../Selectors/list';
import { List } from './List';

@connect(state => ({
  elements: elementsSelector(state),
  viewMode: viewDataSelector(state)
}))
export class ListContainer extends React.Component {
  render() {
    return (
      <List {...this.props} />
    );
  }
}
