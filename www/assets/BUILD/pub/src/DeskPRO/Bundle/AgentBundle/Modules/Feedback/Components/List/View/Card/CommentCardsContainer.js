import React, { Component, PropTypes } from 'react';
import { connect } from 'react-redux';
import { CommentCard } from './CommentCard';
import { idsSelector } from '../../../../Selectors/list';
import { selectedSelector } from '../../../../../Application/Selectors/massActions';
import { collectionSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';

@connect(state => ({
  ids:      idsSelector(state),
  comments: collectionSelectorFactory('FeedbackComment', 'feedback')(state),
  selected: selectedSelector(state),
  people:   collectionSelectorFactory('Person', 'feedback')(state),
  feedback: collectionSelectorFactory('Feedback', 'feedback')(state)
}))
export class CommentCardsContainer extends Component {
  static propTypes = {
    dispatch:       PropTypes.func.isRequired,
    ids:            PropTypes.array.isRequired,
    comments:       PropTypes.object.isRequired,
    toggleSelected: PropTypes.func.isRequired,
    people:         PropTypes.object.isRequired,
    feedback:       PropTypes.object.isRequired,
    selected:       PropTypes.object.isRequired
  };

  renderComment(id) {
    const { dispatch, comments, selected, toggleSelected, people, feedback } = this.props;
    const element = comments.get(id);
    return (
      <CommentCard
        key={id}
        comment={element}
        dispatch={dispatch}
        feedback={feedback.get(element.get('feedback'))}
        selected={selected.includes(id)}
        toggleSelected={toggleSelected}
        author={people.get(element.get('person'))}
      />
    );
  }

  render() {
    const { ids } = this.props;

    return (
      <div>
        {ids.map(id => this.renderComment(id))}
      </div>
    );
  }
}
