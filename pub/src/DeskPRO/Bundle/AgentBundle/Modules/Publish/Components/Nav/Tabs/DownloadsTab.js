import React, {Component, PropTypes} from 'react';
import { SectionsPane, Section, SectionGroupedHeader }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';
import { NestedList } from '../NestedList';
import { NavGroupingPopupContainer } from '../NavGroupingPopupContainer';

export class DownloadsTab extends Component {

  static propTypes = {
    downloads: PropTypes.object.isRequired,
    toggleGroupingVisibility: PropTypes.func.isRequired,
    closeGroupingVisibility: PropTypes.func.isRequired
  };

  constructor(props) {
    super(props);
    this.state = { expanded: false };
  }

  render() {
    const { downloads, toggleGroupingVisibility, closeGroupingVisibility } = this.props;
    return (
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
    );
  }
}
