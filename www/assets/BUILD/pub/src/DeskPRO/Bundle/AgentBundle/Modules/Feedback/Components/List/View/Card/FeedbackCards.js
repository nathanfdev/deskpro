import PropTypes from 'prop-types';
// @flow
import React from 'react';
import { FeedbackCard } from './FeedbackCard';
import { List, Map, fromJS } from 'immutable';

const emptyList = fromJS([]);

export const FeedbackCards = (props:{
  elements: Array<number>,
  selected: List,
  feedback: Map,
  fields: Map,
  people: Map,
  feedbackTypes: Map,
  feedbackStatusCategories: Map,
  toggleSelected:(id:number) => void}) => {
  const { feedback, elements, fields, selected, toggleSelected } = props;
  const { people, feedbackTypes, feedbackStatusCategories } = props;

  return (
    <div>
      {elements.map(id => {
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
      })}
    </div>
  );
};

FeedbackCards.propTypes = {
  feedback:                 PropTypes.object.isRequired,
  fields:                   PropTypes.object.isRequired,
  selected:                 PropTypes.object.isRequired,
  people:                   PropTypes.object.isRequired,
  feedbackTypes:            PropTypes.object.isRequired,
  feedbackStatusCategories: PropTypes.object,
  toggleSelected:           PropTypes.func.isRequired,
  elements:                 PropTypes.array.isRequired
};
