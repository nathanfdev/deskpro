import React, {Component, PropTypes} from 'react';
import { LoadIndicator } from 'DeskPRO/Component/LoadIndicator';
import { SectionsPane, Section, SectionGroupedHeader }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';
import { NestedList } from '../NestedList';

export class NewsTab extends Component {

  static propTypes = {
    loaded: PropTypes.bool.isRequired,
    news: PropTypes.object.isRequired,
    toggleGroupingVisibility: PropTypes.func.isRequired
  };

  render() {
    const { loaded, news, toggleGroupingVisibility} = this.props;
    return (
      <LoadIndicator loaded={loaded}>
        <SectionsPane>
          <Section ref="news">
            <SectionGroupedHeader label="News"
                                  count={news.get('count')}
                                  callback={toggleGroupingVisibility('news')}/>
            <NestedList content="news"
                        items={news.get('nested').toJS()}/>
          </Section>
        </SectionsPane>
      </LoadIndicator>
    );
  }
}
