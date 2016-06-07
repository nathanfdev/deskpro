import React from 'react';
const { Component, PropTypes } = React;
import {
  SectionsPane, Section, SectionGroupedHeader
}
  from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame';
import { NestedList } from '../NestedList';
import { NavGroupingPopup } from '../NavGroupingPopup';

export class NewsTab extends Component {
  static propTypes = {
    news:                     PropTypes.object.isRequired,
    toggleGroupingVisibility: PropTypes.func.isRequired,
    closeGroupingVisibility:  PropTypes.func.isRequired
  };

  constructor(props) {
    super(props);
    this.state = { expanded: false };
  }

  getAttachTarget = () => this.refs.news;

  render = () => {
    const { news, toggleGroupingVisibility, closeGroupingVisibility } = this.props;
    const toggle = toggleGroupingVisibility.bind(this);
    const close  = closeGroupingVisibility.bind(this);

    return (
      <SectionsPane>
        <Section>
          <SectionGroupedHeader
            label="News"
            count={news.get('count')}
            ref="news"
            callback={toggle}
          />
          <NestedList
            content="news"
            items={news.get('nested').toJS()}
          />
          <NavGroupingPopup
            attachTo={this.getAttachTarget}
            content="news"
            visible={this.state.expanded}
            closeGroupingVisibility={close}
            groupedBy={news.get('grouped_by')}
          />
        </Section>
      </SectionsPane>
    );
  }
}
