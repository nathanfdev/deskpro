import React, {Component, PropTypes} from 'react';
import { LoadIndicator } from 'DeskPRO/Component/LoadIndicator';
import { SectionsPane, Section, SectionGroupedHeader, NestedList }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';

export class NewsTab extends Component {

  static propTypes = {
    loaded: PropTypes.bool.isRequired,
    news: PropTypes.object.isRequired,
    toggleGroupingVisibility: PropTypes.func.isRequired,
    onClick: PropTypes.func.isRequired
  };

  render() {
    const { loaded, news, toggleGroupingVisibility, onClick} = this.props;
    return (
      <LoadIndicator loaded={loaded}>
        <SectionsPane>
          <Section ref="news">
            <SectionGroupedHeader label="News"
                                  count={news.get('count')}
                                  callback={toggleGroupingVisibility('news')}/>
            <NestedList items={news.get('nested').toJS()}
                        onClick={onClick}/>
          </Section>
        </SectionsPane>
      </LoadIndicator>
    );
  }
}
