import React, { Component, PropTypes } from 'react';
import { NavFrame, NavFrameHeader, SectionsPane, Section, SectionHeader, TabsPane, Tab, LabelsDictionary }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/NavFrame/index';
import { Pending } from './Pending';
import { StatusTab } from './StatusTab';
import { TypeTab } from './TypeTab';
import { CategoryTab } from './CategoryTab';


export class Nav extends Component {
  static propTypes = {
    groupChoice: PropTypes.func.isRequired,
    dispatch: PropTypes.func.isRequired,
    statuses: PropTypes.object.isRequired,
    labels: PropTypes.array.isRequired,
    types: PropTypes.array.isRequired,
    customCategories: PropTypes.array.isRequired
  };

  render() {
    const { groupChoice, labels, types, toValidateCount, commentsToReviewCount, statuses, customCategories, dispatch, dp_window } = this.props;
    return (
      <NavFrame dispatch={dispatch.bind(this)} dp_window={dp_window}>
        <NavFrameHeader icon="fa-thumbs-up" dispatch={dispatch.bind(this)}>Feedback</NavFrameHeader>

        <SectionsPane>

          <Pending toValidateCount={toValidateCount} commentsToReviewCount={commentsToReviewCount}
                   onClick={groupChoice.bind(this)}/>

          <Section>
            <TabsPane>
              <Tab title="Status">
                <StatusTab statuses={statuses} onClick={groupChoice.bind(this)}/>
              </Tab>

              <Tab title="Labels">
                <LabelsDictionary labels={labels} onClick={groupChoice.bind(this)}/>
              </Tab>
            </TabsPane>

            <TabsPane>
              <Tab title="Type">
                <TypeTab types={types} onClick={groupChoice.bind(this)}/>
              </Tab>
              <Tab title="Categories">
                <CategoryTab customCategories={customCategories} onClick={groupChoice.bind(this)}/>
              </Tab>
            </TabsPane>

          </Section>
        </SectionsPane>
      </NavFrame>
    );
  }

}
