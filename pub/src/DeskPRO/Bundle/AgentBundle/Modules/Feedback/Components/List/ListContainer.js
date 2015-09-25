import React from 'react';
import { List } from './List';
import { connect } from 'react-redux';
import { viewDataSelector, peopleSelector } from '../../Selectors/list';
import { createPeopleRequestSelectors }  from 'DeskPRO/Bundle/AgentBundle/Modules/CRM/RecordStores/Selectors/peopleSelectors';

const feedbackAppUserSel = createPeopleRequestSelectors('feedback');

@connect(state => {
  return ({
    feedback: state.Feedback.list.get('feedback'),
    comments: state.Feedback.list.get('comments'),
    currentContent: state.Feedback.list.get('currentContent'),
    currentViewMode: viewDataSelector(state),
    people: peopleSelector(state)
  });
})

export class ListContainer extends React.Component {
  render() {
    const {feedback, comments, currentContent, currentViewMode, people} = this.props;
    return (
      <List
        feedback={feedback}
        comments={comments}
        currentContent={currentContent}
        currentViewMode={currentViewMode}
        people={people}
        />
    );
  }
}