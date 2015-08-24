import React, { Component, PropTypes } from 'react';
import * as actions from '../../Actions/FeedbackListActions'
import { NavFrame, NavFrameHeader, SectionsPane, Section, SectionHeader, TabsPane, Tab, ListItem, LabelsDictionary }
    from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/NavFrame/index';

export class Nav extends React.Component {


    render() {
        const { labels, types, toValidateCount, commentsToReviewCount, statuses, customCategories, onClick } = this.props;

        let itemKey = 0;

        return (
            <NavFrame>
                <NavFrameHeader icon="fa-thumbs-up">Feedback</NavFrameHeader>
                <SectionsPane>
                    <Section>
                        <SectionHeader>Pending</SectionHeader>
                        <ul>
                            <ListItem count={toValidateCount} label="Feedback to Validate"/>
                            <ListItem count={commentsToReviewCount} label="Comments to Review"/>
                        </ul>
                    </Section>

                    <Section>
                        <TabsPane>
                            <Tab title="Status">
                                <ul>
                                    <ListItem onClick={onClick.bind(this, actions.loadFeedbackList({'status':'new'}))} count={statuses.new} label="New"/>
                                    <ListItem onClick={onClick.bind(this, 'FEEDBACK_COMMENTS_TO_REVIEW')} count={statuses.active.total}
                                              label="Active">
                                        {statuses.active.statuses.map(item =>
                                            <ListItem key={itemKey++}
                                                      count={item.count} label={item.group}/>)}
                                    </ListItem>
                                    <ListItem count={statuses.closed.total} label="Closed">
                                        {statuses.closed.statuses.map(item =>
                                            <ListItem key={itemKey++} count={item.count} label={item.group}/>)}
                                    </ListItem>
                                    <ListItem count={statuses.hidden.total} label="Hidden">
                                        {statuses.hidden.statuses.map(item =>
                                            <ListItem key={itemKey++} count={item.count} label={item.group}/>)}
                                    </ListItem>
                                </ul>
                            </Tab>
                            <Tab title="Labels">
                                <LabelsDictionary labels={labels}/>
                            </Tab>
                        </TabsPane>
                        <TabsPane>
                            <Tab title="Type">
                                <ul>
                                    {types.map(item =>
                                        <ListItem key={itemKey++} count={item.value} label={item.title}/>)}
                                </ul>
                            </Tab>
                            <Tab title="Categories">
                                <ul>
                                    {customCategories.map(item =>
                                        <ListItem key={itemKey++} count={item.count} label={item.group}/>)
                                    }
                                </ul>
                            </Tab>
                        </TabsPane>
                    </Section>
                </SectionsPane>
            </NavFrame>
        );
    }
}