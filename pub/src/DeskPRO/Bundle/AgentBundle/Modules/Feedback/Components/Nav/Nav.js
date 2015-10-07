import React, { Component, PropTypes } from 'react';
import { NavFrame, NavFrameHeader, TabsPane, Tab, LabelsDictionary }
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
    groupChoice: PropTypes.func.isRequired,
    dispatch: PropTypes.func.isRequired,
    currentGroup: PropTypes.object.isRequired,
    statuses: PropTypes.object.isRequired,
    labels: PropTypes.array.isRequired,
    types: PropTypes.array.isRequired,
    customCategories: PropTypes.array.isRequired,
    toValidateCount: PropTypes.number.isRequired,
    commentsToReviewCount: PropTypes.number.isRequired
  };

  render() {
    const { currentGroup, groupChoice, labels, types, toValidateCount, commentsToReviewCount, statuses, customCategories, dispatch, dpWindow, commentsView } = this.props;

    return (
      <NavFrame dispatch={dispatch.bind(this)} dpWindow={dpWindow}>

        <NavFrameHeader icon="fa-thumbs-up" dispatch={dispatch.bind(this)}>
          <FormattedMessage id="feedback.nav.title"/>
        </NavFrameHeader>

        <Pending
          toValidateCount={toValidateCount}
          commentsToReviewCount={commentsToReviewCount}
          currentGroup={currentGroup}
          onClick={groupChoice.bind(this)}
          commentsView={commentsView.bind(this)}
          />

        <TabsPane>
          <Tab title={this.props.intl.formatMessage({id: 'feedback.nav.tabs.status'})}>
            <StatusTab currentGroup={currentGroup} statuses={statuses} onClick={groupChoice.bind(this)}/>
          </Tab>

          <Tab title="Labels">
            <LabelsDictionary labels={labels} onClick={groupChoice.bind(this)}/>
          </Tab>
          <Tab title="Type">
            <TypeTab currentGroup={currentGroup} types={types} onClick={groupChoice.bind(this)}/>
          </Tab>
          <Tab title="Category">
            <CategoryTab currentGroup={currentGroup} customCategories={customCategories}
                         onClick={groupChoice.bind(this)}/>
          </Tab>
        </TabsPane>
      </NavFrame>
    );
  }

}
