import React, { Component, PropTypes } from 'react';
import { Section, SectionHeader } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';
import { ListItemContainer } from './ListItemContainer';

export class Pending extends Component {

  static propTypes = {
    toValidateCount: PropTypes.number.isRequired,
    commentsToReviewCount: PropTypes.number.isRequired,
    commentsView: PropTypes.func.isRequired
  };

  render() {
    const { toValidateCount, commentsToReviewCount, commentsView } = this.props;

    return (
      <Section>
        <SectionHeader>Pending</SectionHeader>
        <ul>
          <ListItemContainer
            count={toValidateCount}
            label="Feedback to Validate"
            listOptions={ {navItem: { awaiting_validation: 1 } } }
            />
          <ListItemContainer
            count={commentsToReviewCount}
            label="Comments to Review"
            listOptions={ {'isComments': true, navItem: { awaiting_validation: 1 } } }
            //onClick={commentsView.bind(this, {name: 'feedback_comments'})}
            />
        </ul>
      </Section>
    );
  }
}
