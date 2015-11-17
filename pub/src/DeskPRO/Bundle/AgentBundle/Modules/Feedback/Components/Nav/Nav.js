import React, { Component, PropTypes } from 'react';
import { NavFrame, NavFrameHeader, NavFrameBody, TabsPaneStatefulContainer, Tab, LabelsDictionary, TabSpinner }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';
import { Pending } from './Pending';
import { StatusTab } from './StatusTab';
import { TypeTab } from './TypeTab';
import { CategoryTab } from './CategoryTab';
import { injectIntl, intlShape, FormattedMessage } from 'react-intl';

@injectIntl
export class Nav extends Component {

  static propTypes = {
    loaded: PropTypes.bool.isRequired,
    intl: intlShape.isRequired,
    dispatch: PropTypes.func.isRequired,
    statuses: PropTypes.object.isRequired,
    labels: PropTypes.object.isRequired,
    types: PropTypes.object.isRequired,
    customCategories: PropTypes.object.isRequired,
    toValidateCount: PropTypes.object.isRequired,
    commentsToReviewCount: PropTypes.object.isRequired
  };

  render() {
    const { labels, loaded, types, toValidateCount, commentsToReviewCount, statuses, customCategories, dispatch, dpWindow } = this.props;

    return (
      <NavFrame dispatch={dispatch.bind(this)} dpWindow={dpWindow}>
        <NavFrameHeader icon="icon-dp-streamline-hand-like-2">
          <FormattedMessage id="feedback.nav.title"/>
        </NavFrameHeader>
        <NavFrameBody>
          <TabSpinner loaded={loaded}>
            <Pending
              toValidateCount={toValidateCount}
              commentsToReviewCount={commentsToReviewCount}
              />

            <TabsPaneStatefulContainer id="tab">
              <Tab title={this.props.intl.formatMessage({id: 'feedback.nav.tabs.status'})}>
                <StatusTab statuses={statuses}/>
              </Tab>

              <Tab title="Labels">
                <LabelsDictionary labels={labels}/>
              </Tab>
              <Tab title="Type">
                <TypeTab types={types}/>
              </Tab>
              <Tab title="Category">
                <CategoryTab customCategories={customCategories}/>
              </Tab>
            </TabsPaneStatefulContainer>
          </TabSpinner>
        </NavFrameBody>
      </NavFrame>
    );
  }

}
