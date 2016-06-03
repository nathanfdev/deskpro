import React, { Component, PropTypes } from 'react';
import { FeedbackCard } from './FeedbackCard';
import Immutable from 'immutable';

const emptyList = Immutable.List();

export class FeedbackCards extends Component {
  static propTypes = {
    feedback:                 PropTypes.object.isRequired,
    fields:                   PropTypes.object.isRequired,
    selected:                 PropTypes.object.isRequired,
    people:                   PropTypes.object.isRequired,
    feedbackTypes:            PropTypes.object.isRequired,
    feedbackStatusCategories: PropTypes.object,
    toggleSelected:           PropTypes.func.isRequired,
    elements:                 PropTypes.object.isRequired
  };

  renderCard(id) {
    const { feedback, fields, selected, toggleSelected } = this.props;
    const { people, feedbackTypes, feedbackStatusCategories } = this.props;
    const element = feedback.get(id);

    return (
      <FeedbackCard
        key={id}
        fields={fields}
        feedback={element}
        selected={selected.includes(id)}
        toggleSelected={toggleSelected}
        author={people.get(element.get('person'))}
        feedbackStatusCategory={feedbackStatusCategories.get(element.get('status_category'))}
        feedbackLabels={element.get('labels') || emptyList}
        type={feedbackTypes.get(element.get('category'))}
      />
    );
  }

  render() {
    const { elements } = this.props;

    return (
      <div>
        {elements.map(id => this.renderCard(id))}
      </div>
    );
  }
}
