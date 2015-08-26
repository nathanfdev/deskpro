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
                            <ListItem onClick={onClick.bind(this, {'awaiting_validation':1})} count={toValidateCount}
                                      label="Feedback to Validate"/>
                            <ListItem count={commentsToReviewCount} label="Comments to Review"/>
                        </ul>
                    </Section>

                    <Section>
                        <TabsPane>
                            <Tab title="Status">
                                <ul>
                                    <ListItem onClick={onClick.bind(this, {'status':'new'})}
                                              count={statuses.new} label="New"/>
                                    <ListItem onClick={onClick.bind(this, {'status':'active'})}
                                              count={statuses.active.total} label="Active">
                                        {statuses.active.statuses.map(item =>
                                            <ListItem key={itemKey++}
                                                      onClick={onClick.bind(this, {'status':'active','status_category':item.group})}
                                                      count={item.count} label={item.group}/>)}
                                    </ListItem>
                                    <ListItem
                                        onClick={onClick.bind(this, {'status':'closed'})}
                                        count={statuses.closed.total} label="Closed">
                                        {statuses.closed.statuses.map(item =>
                                            <ListItem key={itemKey++} count={item.count} label={item.group}
                                                      onClick={onClick.bind(this, {'status':'closed','status_category':item.group})}
                                                />)}
                                    </ListItem>
                                    <ListItem
                                        onClick={onClick.bind(this, {'status':'hidden'})}
                                        count={statuses.hidden.total} label="Hidden">
                                        {statuses.hidden.statuses.map(item =>
                                            <ListItem key={itemKey++} count={item.count} label={item.group}/>)}
                                    </ListItem>
                                </ul>
                            </Tab>
                            <Tab title="Labels">
                                <LabelsDictionary labels={labels}
                                                  onClick={onClick.bind(this)}
                                    />
                            </Tab>
                        </TabsPane>
                        <TabsPane>
                            <Tab title="Type">
                                <ul>
                                    {types.map(item =>
                                        <ListItem key={itemKey++} count={item.value} label={item.title}
                                                  onClick={onClick.bind(this, {'category':item.id})}
                                            />)}
                                </ul>
                            </Tab>
                            <Tab title="Categories">
                                <ul>
                                    {customCategories.map(item =>
                                        <ListItem key={itemKey++} count={item.count} label={item.group}
                                                  onClick={onClick.bind(this, {'custom_category':item.group})}
                                            />)
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