import React from 'react';
import { List } from './List';
import { connect } from 'react-redux';
import { viewDataSelector } from '../../Selectors/list';

@connect(state => {
  return ({
    elements: state.Feedback.list.get('feedback'),
    currentViewMode: viewDataSelector(state)
  });
})

export class ListContainer extends React.Component {

  render() {
    const {elements, currentViewMode} = this.props;
    return (
      <List elements={elements} currentViewMode={currentViewMode}/>
    );
  }
}