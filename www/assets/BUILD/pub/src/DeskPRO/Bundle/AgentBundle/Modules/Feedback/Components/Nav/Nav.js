import PropTypes from 'prop-types';
import React, { Component } from 'react';
import {
  LabelsDictionary,
  NavFrame,
  NavFrameHeaderContainer,
  NavFrameBody,
  TabsPaneStatefulContainer,
  Tab
}
  from '../../../Common/Components/NavFrame';
import { Pending } from './Pending';
import { StatusTab } from './StatusTab';
import { TypeTab } from './TypeTab';
import { CategoryTab } from './CategoryTab';
import { injectIntl, intlShape, FormattedMessage } from 'react-intl';

@injectIntl
export class Nav extends Component {

  static propTypes = {
    onLabelClick:          PropTypes.func.isRequired,
    isLoaded:              PropTypes.bool,
    intl:                  intlShape.isRequired,
    statuses:              PropTypes.object.isRequired,
    labels:                PropTypes.object.isRequired,
    types:                 PropTypes.object,
    categories:            PropTypes.object,
    feedbackToReviewCount: PropTypes.object.isRequired,
    commentsToReviewCount: PropTypes.object.isRequired
  };

  render() {
    const { labels, isLoaded, onLabelClick, types, statuses, categories } = this.props;
    const { feedbackToReviewCount, commentsToReviewCount } = this.props;

    return (
      <NavFrame>
        <NavFrameHeaderContainer icon="icon-dp-streamline-hand-like-2">
          <FormattedMessage id="feedback.nav.title" />
        </NavFrameHeaderContainer>
        <NavFrameBody isLoaded={isLoaded}>
          <Pending
            feedbackToReviewCount={feedbackToReviewCount}
            commentsToReviewCount={commentsToReviewCount}
          />

          <TabsPaneStatefulContainer id="tab">
            <Tab title={this.props.intl.formatMessage({ id: 'feedback.nav.tabs.status' })}>
              <StatusTab statuses={statuses} />
            </Tab>

            <Tab title="Labels">
              <LabelsDictionary labels={labels} onClick={onLabelClick} />
            </Tab>
            <Tab title="Type">
              <TypeTab types={types} />
            </Tab>
            <Tab title="Category">
              <CategoryTab categories={categories} />
            </Tab>
          </TabsPaneStatefulContainer>
        </NavFrameBody>
      </NavFrame>
    );
  }

}
