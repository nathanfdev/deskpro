import React, { Component, PropTypes } from 'react';
import { NavFrame, NavFrameHeaderContainer, NavFrameBody, TabsPaneStatefulContainer, Tab, LabelsDictionary }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame';
import { Pending } from './Pending';
import { StatusTab } from './StatusTab';
import { TypeTab } from './TypeTab';
import { CategoryTab } from './CategoryTab';
import { injectIntl, intlShape, FormattedMessage } from 'react-intl';
import { applyParams } from '../../Actions/FeedbackListActions';

@injectIntl
export class Nav extends Component {

  static propTypes = {
    isLoaded: PropTypes.bool.isRequired,
    intl: intlShape.isRequired,
    statuses: PropTypes.object.isRequired,
    labels: PropTypes.object.isRequired,
    types: PropTypes.object,
    categories: PropTypes.object,
    toValidateCount: PropTypes.object.isRequired,
    commentsToReviewCount: PropTypes.object.isRequired
  };

  // @todo move this callback to the NavContainer
  onLabelClick = (params) => {
    this.props.dispatch(applyParams({ navItem: { [params.name]: params.value } }));
  };

  render() {
    const { labels, isLoaded, types, toValidateCount, commentsToReviewCount, statuses, categories } = this.props;

    return (
      <NavFrame>
        <NavFrameHeaderContainer icon="icon-dp-streamline-hand-like-2">
          <FormattedMessage id="feedback.nav.title"/>
        </NavFrameHeaderContainer>
        <NavFrameBody isLoaded={isLoaded}>
          <Pending toValidateCount={toValidateCount}
                   commentsToReviewCount={commentsToReviewCount}/>

          <TabsPaneStatefulContainer id="tab">
            <Tab title={this.props.intl.formatMessage({id: 'feedback.nav.tabs.status'})}>
              <StatusTab statuses={statuses} />
            </Tab>

            <Tab title="Labels">
              <LabelsDictionary labels={labels} onClick={this.onLabelClick}/>
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
