import React from 'react';
import { connect } from 'react-redux';
import { allSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import { LinkedItem } from './LinkedItem';

@connect(state => ({
  tickets:  allSelectorFactory('Ticket')(state),
  articles: allSelectorFactory('Article')(state),
  chats:    allSelectorFactory('UserChat')(state)
}))
export class LinkedItemContainer extends React.Component {

  render() {
    return (
      <LinkedItem {...this.props} />
    );
  }
}
