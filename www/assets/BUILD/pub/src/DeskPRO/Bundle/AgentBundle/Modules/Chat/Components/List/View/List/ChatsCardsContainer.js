import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { ChatCard } from './ChatCard';
import { selectedSelector } from '../../../../../Application/Selectors/massActions';
import { collectionSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import { connect } from 'react-redux';

@connect(state => ({
  chats:       collectionSelectorFactory('UserChat', 'chats')(state),
  selected:    selectedSelector(state),
  people:      collectionSelectorFactory('Person', 'chats')(state),
  departments: collectionSelectorFactory('Department', 'all_chat')(state)
}))
export class ChatsCardsContainer extends Component {
  static propTypes = {
    chats:          PropTypes.object.isRequired,
    people:         PropTypes.object.isRequired,
    departments:    PropTypes.object.isRequired,
    selected:       PropTypes.object.isRequired,
    toggleSelected: PropTypes.func.isRequired,
    fields:         PropTypes.object.isRequired
  };

  renderCard(element) {
    const { toggleSelected, people, departments, selected, fields } = this.props;

    return (
      <ChatCard
        key={element.get('id')}
        author={people.get(element.get('person'))}
        agent={people.get(element.get('agent'))}
        department={departments.get(element.get('department'))}
        chat={element}
        selected={selected.includes(element.get('id'))}
        toggleSelected={toggleSelected}
        fields={fields}
      />
    );
  }

  render() {
    return (
      <div>
        {this.props.chats.entrySeq().map(([id, chat]) => this.renderCard(chat))}
      </div>
    );
  }

}
