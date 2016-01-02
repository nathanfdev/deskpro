import React, { Component, PropTypes } from 'react';
import { NavFrame, NavFrameHeader, NavFrameBody, TabsPaneStatefulContainer, Tab, LabelsDictionary }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';
import { LoadIndicator } from 'DeskPRO/Component/LoadIndicator';
import { Pending } from './Pending';
import { StatusTab } from './StatusTab';
import { TypeTab } from './TypeTab';
import { CategoryTab } from './CategoryTab';
import { injectIntl, intlShape, FormattedMessage } from 'react-intl';
import { applyParams } from '../../Actions/FeedbackListActions';

@injectIntl
export class Nav extends Component {

  static propTypes = {
    loaded: PropTypes.bool.isRequired,
    intl: intlShape.isRequired,
    dispatch: PropTypes.func.isRequired,
    statuses: PropTypes.object.isRequired,
    labels: PropTypes.object.isRequired,
    types: PropTypes.object,
    categories: PropTypes.object,
    toValidateCount: PropTypes.object.isRequired,
    commentsToReviewCount: PropTypes.object.isRequired,
    dpWindow: PropTypes.object.isRequired
  };

  onLabelClick = (params) => {
    const { dispatch } = this.props;
    dispatch(applyParams({ navItem: { [params.name]: params.value } }));
  };

  render() {
    const { labels, loaded, types, toValidateCount, commentsToReviewCount, statuses, categories, dispatch, dpWindow } = this.props;
    const currentApp = dpWindow.get('activeAppId');

    return (
      <NavFrame dispatch={dispatch.bind(this)} dpWindow={dpWindow}>
        <NavFrameHeader icon="icon-dp-streamline-hand-like-2" currentApp={currentApp}>
          <FormattedMessage id="feedback.nav.title"/>
        </NavFrameHeader>
        <NavFrameBody>
          <Pending loaded={loaded}
                   toValidateCount={toValidateCount}
                   commentsToReviewCount={commentsToReviewCount}/>

          <TabsPaneStatefulContainer id="tab">
            <Tab title={this.props.intl.formatMessage({id: 'feedback.nav.tabs.status'})}>
              <StatusTab statuses={statuses} loaded={loaded}/>
            </Tab>

            <Tab title="Labels">
              <LoadIndicator loaded={loaded}>
                <LabelsDictionary labels={labels} onClick={this.onLabelClick}/>
              </LoadIndicator>
            </Tab>
            <Tab title="Type">
              <TypeTab types={types} loaded={loaded}/>
            </Tab>
            <Tab title="Category">
              <CategoryTab categories={categories} loaded={loaded}/>
            </Tab>
          </TabsPaneStatefulContainer>
        </NavFrameBody>
      </NavFrame>
    );
  }

}
