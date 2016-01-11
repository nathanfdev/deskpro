import React, {Component, PropTypes} from 'react';
import { Section, SectionHeader }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';
import { ListItemContainer } from '../ListItemContainer';

export class AllChats extends Component {
  static propTypes = {
    labels: PropTypes.object.isRequired,
    all: PropTypes.object.isRequired
  };

  renderItem(item, index) {
    const { labels } = this.props;
    const groupBy = item.get('type');
    const group = item.get('id');
    const count = item.get('count');
    const label = labels[groupBy] && labels[groupBy].get(group) ? labels[groupBy].get(group) : '...';

    return (
      <ListItemContainer groupBy={groupBy}
                         count={count}
                         label={label}
                         key={index}
                         listOptions={{navItem: {[groupBy]: group}}}/>
    );
  }

  render() {
    const {all} = this.props;

    return (
      <Section ref="allSection">
        <SectionHeader>
          All Chats
          <div className="list-counter-bucket">
            <a className="list-counter-dropdown active" href="#" onClick={()=>{}}>
              <span>&nbsp;</span>
              <i className="fa fa-angle-down"></i>
            </a>
            <a className="list-counter active" href="#">{all.get('count')}</a>
          </div>
        </SectionHeader>

        <ul>
          {all.get('nested').map((item, index) => this.renderItem(item, index))}
        </ul>
      </Section>
    );
  }
}