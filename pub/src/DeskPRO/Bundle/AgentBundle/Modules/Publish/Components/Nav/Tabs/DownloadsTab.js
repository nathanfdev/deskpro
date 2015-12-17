import React, {Component, PropTypes} from 'react';
import { LoadIndicator } from 'DeskPRO/Component/LoadIndicator';
import { SectionsPane, Section, SectionGroupedHeader, NestedList }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';

export class DownloadsTab extends Component {

  static propTypes = {
    loaded: PropTypes.bool.isRequired,
    downloads: PropTypes.object.isRequired,
    toggleGroupingVisibility: PropTypes.func.isRequired,
    onClick: PropTypes.func.isRequired
  };

  render() {
    const { loaded, downloads, toggleGroupingVisibility, onClick} = this.props;
    return (
      <LoadIndicator loaded={loaded}>
        <SectionsPane>
          <Section ref="downloads">
            <SectionGroupedHeader label="Downloads"
                                  count={downloads.get('count')}
                                  callback={toggleGroupingVisibility('downloads')}/>
            <NestedList items={downloads.get('nested').toJS()}
                        onClick={onClick}/>
          </Section>
        </SectionsPane>
      </LoadIndicator>
    );
  }
}
