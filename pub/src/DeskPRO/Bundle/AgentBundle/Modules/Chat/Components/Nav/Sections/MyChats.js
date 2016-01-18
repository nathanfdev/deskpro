import React, {Component, PropTypes} from 'react';
import { Section, SectionHeader }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';
import { ListItemContainer } from '../ListItemContainer';
import { NavGroupingPopupContainer } from '../NavGroupingPopupContainer';

export class MyChats extends Component {
  static propTypes = {
    my: PropTypes.object.isRequired
  };

  componentWillMount() {
    this.state = { expanded: false };
  }

  close() {
    this.setState({ 'expanded': false });
  }

  toggle(event) {
    event.preventDefault();
    this.setState({ 'expanded': !this.state.expanded });
  }

  renderItem(item, index) {
    const groupBy = item.get('type');
    const group = item.get('id');
    const count = item.get('count');
    const label = item.get('title');

    return (
      <ListItemContainer groupBy={groupBy}
                         count={count}
                         key={index}
                         label={label}
                         listOptions={{navItem: {[groupBy]: group}, agent: 'me'}}/>
    );
  }

  render() {
    const { my } = this.props;

    return (
      <Section ref="mySection">
        <SectionHeader>
          My Chats
          <div className="list-counter-bucket">
            <a className="list-counter-dropdown active" href="#" onClick={this.toggle.bind(this)}>
              <span>&nbsp;</span>
              <i className="fa fa-angle-down"></i>
            </a>
            <a className="list-counter active" href="#">{my.get('count')}</a>
          </div>
        </SectionHeader>

        <ul>
          {my.get('nested').map((item, index) => this.renderItem(item, index))}
        </ul>
        <NavGroupingPopupContainer attachTo={this.refs.mySection}
                                   content="my"
                                   visible={this.state.expanded}
                                   closeGroupingVisibility={this.close.bind(this)}
                                   groupedBy={my.get('grouped_by')}/>
      </Section>
    );
  }
}