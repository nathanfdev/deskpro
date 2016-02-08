import React, { Component, PropTypes } from 'react';
import { Section, SectionHeader, ListItem } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame';
import { ListItemContainer } from './ListItemContainer';

export class Pending extends Component {

  static propTypes = {
    toValidateCount: PropTypes.object.isRequired,
    commentsToReviewCount: PropTypes.object.isRequired
  };

  render() {
    const { toValidateCount, commentsToReviewCount } = this.props;

    return (
      <Section>
        <SectionHeader>Pending</SectionHeader>
        <ul>
          <ListItemContainer label="Feedback to Review"
                             listOptions={{isComments: false, navItem: {awaiting_validation: 1}}}>

            <ListItem count={toValidateCount}
                      label="Feedback to Review"/>
          </ListItemContainer>

          <ListItemContainer label="Comments to Review"
                             listOptions={{isComments: true, navItem: {awaiting_validation: 1}}}>

            <ListItem count={commentsToReviewCount}
                      label="Comments to Review"
              />

          </ListItemContainer>
        </ul>
      </Section>
    );
  }
}
