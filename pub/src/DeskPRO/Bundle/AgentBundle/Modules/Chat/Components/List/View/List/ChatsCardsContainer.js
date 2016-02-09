import React, {Component, PropTypes} from 'react';
import { ChatCard } from './ChatCard';
import { selectedSelector } from '../../../../../Application/Selectors/massActions';
import { peopleSelector } from '../../../../Selectors/recordStores';
import { collectionSelectorFactory } from 'DeskPRO/Bundle/AgentBundle/Modules/RecordsStore';
import { connect } from 'react-redux';

@connect(state => ({
  chats: collectionSelectorFactory('Chat', 'chats')(state),
  selected: selectedSelector(state),
  people: peopleSelector(state),
  departments: collectionSelectorFactory('Department', 'chats')(state)
}))
export class ChatsCardsContainer extends Component {
  static propTypes = {
    ids: PropTypes.array.isRequired,
    chats: PropTypes.object.isRequired,
    people: PropTypes.object.isRequired,
    departments: PropTypes.object.isRequired,
    selected: PropTypes.object.isRequired,
    toggleSelected: PropTypes.func.isRequired
  };

  renderCard(element) {
    const { toggleSelected, people, departments, selected } = this.props;

    return (
      <ChatCard key={id}
                author={people.get(element.get('person'))}
                agent={people.get(element.get('agent'))}
                department={departments.get(element.get('department'))}
                chat={element}
                selected={selected.includes(id)}
                toggleSelected={toggleSelected}/>
    );
  }

  render() {
    return (
      <div>
        {this.props.chats.map(chat => this.renderCard(chat))}
      </div>
    );
  }

}