import React, { Component, PropTypes } from 'react';
import { NavFrame, NavFrameHeader, NavFrameBody, TabsPaneStatefulContainer, Tab, LabelsDictionary }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';
import { Pending } from './Pending';
import { StatusTab } from './StatusTab';
import { TypeTab } from './TypeTab';
import { CategoryTab } from './CategoryTab';
import { injectIntl, intlShape, FormattedMessage } from 'react-intl';

@injectIntl
export class Nav extends Component {

  static propTypes = {
    intl: intlShape.isRequired,
    dispatch: PropTypes.func.isRequired,
    statuses: PropTypes.object.isRequired,
    labels: PropTypes.array.isRequired,
    types: PropTypes.array.isRequired,
    customCategories: PropTypes.array.isRequired,
    toValidateCount: PropTypes.number.isRequired,
    commentsToReviewCount: PropTypes.number.isRequired
  };

  render() {
    const { labels, types, toValidateCount, commentsToReviewCount, statuses, customCategories, dispatch, dpWindow, commentsView } = this.props;

    return (
      <NavFrame dispatch={dispatch.bind(this)} dpWindow={dpWindow}>
        <NavFrameHeader icon="icon-dp-streamline-hand-like-2">
          <FormattedMessage id="feedback.nav.title"/>
        </NavFrameHeader>
        <NavFrameBody>
          <Pending
            toValidateCount={toValidateCount}
            commentsToReviewCount={commentsToReviewCount}
            commentsView={commentsView.bind(this)}
          />

          <TabsPaneStatefulContainer id="tab">
            <Tab title={this.props.intl.formatMessage({id: 'feedback.nav.tabs.status'})}>
              <StatusTab statuses={statuses} />
            </Tab>

            <Tab title="Labels">
              <LabelsDictionary labels={labels} />
            </Tab>
            <Tab title="Type">
              <TypeTab types={types} />
            </Tab>
            <Tab title="Category">
              <CategoryTab customCategories={customCategories} />
            </Tab>
          </TabsPaneStatefulContainer>
        </NavFrameBody>
      </NavFrame>
    );
  }

}
