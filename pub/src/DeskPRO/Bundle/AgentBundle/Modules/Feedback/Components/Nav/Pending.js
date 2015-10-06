import React, { Component, PropTypes } from 'react';
import { Section, SectionHeader, ListItem }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';

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
          <div onClick={onClick.bind(this, {name: 'awaiting_validation', value: 1})}>
            <ListItem
              count={toValidateCount}
              label="Feedback to Validate"
              active={currentGroup.name === 'awaiting_validation'}
              />
          </div>
          <div onClick={commentsView.bind(this, {name: 'feedback_comments'})}>
            <ListItem
              count={commentsToReviewCount}
              label="Comments to Review"
              active={currentGroup.name === 'feedback_comments'}
              />
          </div>
        </ul>
      </Section>
    );
  }
}
