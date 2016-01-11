import React, {Component, PropTypes} from 'react';
import { ChatCard } from './ChatCard';
import { elementsSelector, selectedSelector } from '../../../../Selectors/list';
import { chatsSelector, peopleSelector, departmentsSelector } from '../../../../Selectors/recordStores';

import { connect } from 'react-redux';
@connect(state => {
  return ({
    ids: elementsSelector(state),
    chats: chatsSelector(state),
    selected: selectedSelector(state),
    people: peopleSelector(state),
    departments: departmentsSelector(state)
  });
})
export class ChatsCardsContainer extends Component {
  static propTypes = {
    ids: PropTypes.array.isRequired,
    chats: PropTypes.object.isRequired,
    people: PropTypes.object.isRequired,
    departments: PropTypes.object.isRequired,
    selected: PropTypes.object.isRequired,
    toggleSelected: PropTypes.func.isRequired
  };

  renderCard(id) {
    const { chats, toggleSelected, people, departments } = this.props;
    const element = chats.get(id);

    return (
      <ChatCard key={id}
                author={people.get(element.get('person'))}
                agent={people.get(element.get('agent'))}
                department={departments.get(element.get('department'))}
                chat={element}
                toggleSelected={toggleSelected}/>
    );
  }

  render() {
    return (
      <div>
        {this.props.ids.map(id => this.renderCard(id))}
      </div>
    );
  }

}