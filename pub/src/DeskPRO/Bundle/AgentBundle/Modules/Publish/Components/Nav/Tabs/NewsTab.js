import React, {Component, PropTypes} from 'react';
import { SectionsPane, Section, SectionGroupedHeader }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';
import { NestedList } from '../NestedList';
import { NavGroupingPopupContainer } from '../NavGroupingPopupContainer';

export class NewsTab extends Component {
  static propTypes = {
    news: PropTypes.object.isRequired,
    toggleGroupingVisibility: PropTypes.func.isRequired,
    closeGroupingVisibility: PropTypes.func.isRequired
  };

  constructor(props) {
    super(props);
    this.state = { expanded: false };
  }

  render() {
    const { news, toggleGroupingVisibility, closeGroupingVisibility} = this.props;
    return (
      <SectionsPane>
        <Section>
          <SectionGroupedHeader label="News"
                                count={news.get('count')}
                                ref="news"
                                callback={toggleGroupingVisibility.bind(this)}/>
          <NestedList content="news"
                      items={news.get('nested').toJS()}/>
          <NavGroupingPopupContainer attachTo={this.refs.news}
                                     content="news"
                                     visible={this.state.expanded}
                                     closeGroupingVisibility={closeGroupingVisibility.bind(this)}
                                     groupedBy={news.get('grouped_by')}/>
        </Section>
      </SectionsPane>
    );
  }
}
