import React, { Component, PropTypes } from 'react';
import { Section, SectionHeader } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';
import { FeedbackListItem } from './FeedbackListItem';

export class Pending extends Component {

  static propTypes = {
    toValidateCount: PropTypes.number.isRequired,
    commentsToReviewCount: PropTypes.number.isRequired,
    onClick: PropTypes.func.isRequired,
    commentsView: PropTypes.func.isRequired,
    currentGroup: PropTypes.object.isRequired
  };

  render() {
    const { toValidateCount, commentsToReviewCount, onClick, currentGroup, commentsView } = this.props;

    return (
      <Section>
        <SectionHeader>Pending</SectionHeader>
        <ul>
          <FeedbackListItem
            count={toValidateCount}
            label="Feedback to Validate"
            onClick={onClick({name: 'awaiting_validation', value: 1})}
          />
          <FeedbackListItem
            count={commentsToReviewCount}
            label="Comments to Review"
            onClick={commentsView.bind(this, {name: 'feedback_comments'})}
          />
        </ul>
      </Section>
    );
  }
}
