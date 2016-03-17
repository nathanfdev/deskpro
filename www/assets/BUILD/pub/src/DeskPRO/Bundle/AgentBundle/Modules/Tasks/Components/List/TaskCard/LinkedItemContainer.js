import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { allSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import { articlesSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Publish/Selectors/recordStores';
import { allChatsSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Chat/Selectors/nav';
import { LinkedItem } from './LinkedItem';


@connect(state => ({
  tickets: allSelectorFactory('Ticket')(state)
}))

export class LinkedItemContainer extends React.Component {

  render() {
    return (
      <LinkedItem {...this.props} />
    );
  }
}
