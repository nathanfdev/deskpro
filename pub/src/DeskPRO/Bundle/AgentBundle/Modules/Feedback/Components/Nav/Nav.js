import React, { Component, PropTypes } from 'react';
import { NavFrame, NavFrameHeader, SectionsPane, Section, SectionHeader, TabsPane, Tab, LabelsDictionary }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/NavFrame/index';
import { Pending } from './Pending';
import { StatusTab } from './StatusTab';
import { TypeTab } from './TypeTab';
import { CategoryTab } from './CategoryTab';
import * as actions from '../../Actions/FeedbackListActions'
import $ from "jquery";

import { connect } from 'redux/react';
@connect(state => state.FeedbackList)

export class Nav extends Component {

  constructor(props) {
    super(props);
    const { dispatch } = this.props;
    dispatch(actions.feedbackToValidate());
    dispatch(actions.commentsToReview());
    dispatch(actions.feedbackLabels());
    dispatch(actions.feedbackTypes());
    dispatch(actions.feedbackCustomCategories());
    dispatch(actions.feedbackNew());
    dispatch(actions.feedbackActiveStatus());
    dispatch(actions.feedbackClosedStatus());
    dispatch(actions.feedbackHiddenStatus());
  }

  render() {
    const { labels, types, toValidateCount, commentsToReviewCount, statuses, customCategories, dispatch, dp_window } = this.props;

    return (
      <NavFrame dispatch={dispatch.bind(this)} dp_window={dp_window}>
        <NavFrameHeader icon="fa-thumbs-up" dispatch={dispatch.bind(this)}>Feedback</NavFrameHeader>

        <SectionsPane>

          <Pending toValidateCount={toValidateCount} commentsToReviewCount={commentsToReviewCount}
                   onClick={this.groupChoice.bind(this)}/>

          <Section>
            <TabsPane>
              <Tab title="Status">
                <StatusTab statuses={statuses} onClick={this.groupChoice.bind(this)}/>
              </Tab>

              <Tab title="Labels">
                <LabelsDictionary labels={labels} onClick={this.groupChoice.bind(this)}/>
              </Tab>
            </TabsPane>

            <TabsPane>
              <Tab title="Type">
                <TypeTab types={types} onClick={this.groupChoice.bind(this)}/>
              </Tab>
              <Tab title="Categories">
                <CategoryTab customCategories={customCategories} onClick={this.groupChoice.bind(this)}/>
              </Tab>
            </TabsPane>

          </Section>
        </SectionsPane>
      </NavFrame>
    );
  }

  groupChoice(params, event) {
    event.preventDefault();
    event.stopPropagation();
    $('.sidebar-list a.item, .sidebar-list a.item-label').removeClass('active');
    $(event.target).closest('a').addClass('active');
    const {dispatch, sort, order, filters } = this.props;
    dispatch(actions.changeQueryState(params, sort, order, filters));
  }

}
