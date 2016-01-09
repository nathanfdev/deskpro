import React, {Component, PropTypes} from 'react';
import { ChatCard } from './ChatCard';
import { elementsSelector, selectedSelector } from '../../../../Selectors/list';
import { chatsSelector } from '../../../../Selectors/recordStores';

import { connect } from 'react-redux';
@connect(state => {
  return ({
    ids: elementsSelector(state),
    chats: chatsSelector(state),
    selected: selectedSelector(state)
  });
})
export class ChatsCardsContainer extends Component {
  static propTypes = {
    ids: PropTypes.array.isRequired,
    chats: PropTypes.object.isRequired,
    selected: PropTypes.object.isRequired,
    toggleSelected: PropTypes.func.isRequired
  };

  renderCard(id) {
    const { chats, toggleSelected } = this.props;
    const element = chats.get(id);
    console.log(element);
    return (
      <ChatCard key={id}
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