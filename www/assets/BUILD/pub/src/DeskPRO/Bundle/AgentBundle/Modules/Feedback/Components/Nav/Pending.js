import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { Section, SectionHeader, ListItem } from '../../../Common/Components/NavFrame';
import { ListItemContainer } from './ListItemContainer';

export class Pending extends Component {

  static propTypes = {
    feedbackToReviewCount: PropTypes.object.isRequired,
    commentsToReviewCount: PropTypes.object.isRequired
  };

  render() {
    const { feedbackToReviewCount, commentsToReviewCount } = this.props;

    const fCount = feedbackToReviewCount ? feedbackToReviewCount.get('count') : 0;
    const cCount = commentsToReviewCount ? commentsToReviewCount.get('count') : 0;

    return (
      <Section>
        <SectionHeader>Pending</SectionHeader>
        <ul>
          <ListItemContainer
            label="Feedback to Review"
            listOptions={{ isComments: false, navItem: { awaiting_validation: 1 } }}
          >
            <ListItem count={fCount} label="Feedback to Review" />
          </ListItemContainer>

          <ListItemContainer
            label="Comments to Review"
            listOptions={{ isComments: true, navItem: { awaiting_validation: 1 } }}
          >

            <ListItem
              count={cCount}
              label="Comments to Review"
            />

          </ListItemContainer>
        </ul>
      </Section>
    );
  }
}
