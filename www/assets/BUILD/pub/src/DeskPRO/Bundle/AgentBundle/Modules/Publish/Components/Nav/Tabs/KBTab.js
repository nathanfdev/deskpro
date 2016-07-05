import React, { Component, PropTypes } from 'react';
import {
  SectionsPane, Section, SectionHeader, ButtonsPane, Button
}
  from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame';
import { NestedList } from '../NestedList';
import { NavGroupingPopup } from '../NavGroupingPopup';

export class KBTab extends Component {
  static propTypes = {
    articles:                 PropTypes.object.isRequired,
    toggleGroupingVisibility: PropTypes.func.isRequired,
    closeGroupingVisibility:  PropTypes.func.isRequired
  };

  constructor(props) {
    super(props);
    this.state = { expanded: false };
  }

  getAttachTarget = () => this.refs.articles;

  render = () => {
    const { articles, toggleGroupingVisibility, closeGroupingVisibility } = this.props;
    const toggle = toggleGroupingVisibility.bind(this);
    const close  = closeGroupingVisibility.bind(this);

    return (
      <div>
        <SectionsPane>
          <Section>
            <SectionHeader
              label="Knowledgebase"
              ref="articles"
              count={articles.get('count')}
              callback={toggle}
            />
            <NestedList
              content="articles"
              groupedBy={articles.get('grouped_by')}
              items={articles.get('nested').toJS()}
            />
            <NavGroupingPopup
              attachTo={this.getAttachTarget}
              content="articles"
              visible={this.state.expanded}
              closeGroupingVisibility={close}
              groupedBy={articles.get('grouped_by')}
            />
          </Section>
        </SectionsPane>

        <ButtonsPane>
          <Button title="Glossary" icon="fa-quote-left" />
          <Button title="Search" icon="fa-search" />
          <Button title="Comments" icon="fa-comments-o" />
        </ButtonsPane>
      </div>
    );
  }
}
