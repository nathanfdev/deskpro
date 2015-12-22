import React, {Component, PropTypes} from 'react';
import { LoadIndicator } from 'DeskPRO/Component/LoadIndicator';
import { SectionsPane, Section, SectionGroupedHeader }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';
import { NestedList } from '../NestedList';

export class DownloadsTab extends Component {

  static propTypes = {
    loaded: PropTypes.bool.isRequired,
    downloads: PropTypes.object.isRequired,
    toggleGroupingVisibility: PropTypes.func.isRequired
  };

  render() {
    const { loaded, downloads, toggleGroupingVisibility } = this.props;
    return (
      <LoadIndicator loaded={loaded}>
        <SectionsPane>
          <Section ref="downloads">
            <SectionGroupedHeader label="Downloads"
                                  count={downloads.get('count')}
                                  callback={toggleGroupingVisibility('downloads')}/>
            <NestedList content="downloads"
                        items={downloads.get('nested').toJS()}/>
          </Section>
        </SectionsPane>
      </LoadIndicator>
    );
  }
}
