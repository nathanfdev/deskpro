import React, {Component, PropTypes} from 'react';
import { LoadIndicator } from 'DeskPRO/Component/LoadIndicator';
import { SectionsPane, Section, SectionGroupedHeader }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';
import { NestedList } from '../NestedList';
import { NavGroupingPopupContainer } from '../NavGroupingPopupContainer';

export class DownloadsTab extends Component {

  static propTypes = {
    loaded: PropTypes.bool.isRequired,
    downloads: PropTypes.object.isRequired,
    toggleGroupingVisibility: PropTypes.func.isRequired,
    closeGroupingVisibility: PropTypes.func.isRequired
  };

  constructor(props) {
    super(props);
    this.state = { expanded: false };
  }

  render() {
    const { loaded, downloads, toggleGroupingVisibility, closeGroupingVisibility } = this.props;
    return (
      <LoadIndicator loaded={loaded}>
        <SectionsPane>
          <Section>
            <SectionGroupedHeader label="Downloads"
                                  count={downloads.get('count')}
                                  ref="downloads"
                                  callback={toggleGroupingVisibility.bind(this)}/>
            <NestedList content="downloads"
                        items={downloads.get('nested').toJS()}/>
            <NavGroupingPopupContainer attachTo={this.refs.downloads}
                                       content="downloads"
                                       visible={this.state.expanded}
                                       closeGroupingVisibility={closeGroupingVisibility.bind(this)}
                                       groupedBy={downloads.get('grouped_by')}/>
          </Section>
        </SectionsPane>
      </LoadIndicator>
    );
  }
}
