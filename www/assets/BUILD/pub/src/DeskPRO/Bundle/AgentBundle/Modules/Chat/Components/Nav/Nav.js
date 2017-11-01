import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { pureRender } from 'DeskPRO/Component/Ampliflux';
import { immutableCountsListShape } from 'DeskPRO/Bundle/AppBundle/Shapes';
import { Section, SectionHeader, ListItem, NavFrame, NavFrameHeaderContainer, NavFrameBody, SectionsPane }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame';
import { ListItemContainer } from './ListItemContainer';
import { ListGroupingModal } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame';

@pureRender
export class Nav extends Component {
  static propTypes = {
    counts:   immutableCountsListShape,
    isLoaded: PropTypes.bool.isRequired,
    changeCountGroupingCallbackFactory: PropTypes.func
  };

  static groupingOptions = [
    { value: 'agent', label: 'Agent' },
    { value: 'department', label: 'Department' },
    { value: 'date_period', label: 'Date Created' }
  ];

  componentWillMount() {
    this.state = { visibleGrouping: null /* count ID */ };
  }

  showGroupingModalFor = count => this.setState({visibleGrouping: count.get('id')});
  hideGroupingModal    = ()    => this.setState({visibleGrouping: null});

  renderItem(parentCountId, nestedCount, index) {
    const groupBy = nestedCount.get('type');
    const group   = nestedCount.get('id');
    const count   = nestedCount.get('count');
    const label   = nestedCount.get('title') || '-';

    return (
      <ListItemContainer
        label={label}
        key={index}
        parentCountId={parentCountId}
        listOptions={{ navItem: { [groupBy]: group } }}
      >
        <ListItem count={count} label={label} />
      </ListItemContainer>
    );
  };

  renderListGroupingModal(count) {
    const attachTo = this.refs[this.getRefName(count)];
    const visible  = this.state.visibleGrouping == count.get('id');
    const title    = count.get('title');
    const options  = Nav.groupingOptions;
    const selected = count.get('grouped_by');

    const close = this.hideGroupingModal;
    const apply = (grouping) => {
      const changeCountGrouping = this.props.changeCountGroupingCallbackFactory(count.get('id'));
      changeCountGrouping(grouping);
      close();
    };

    const modal = {attachTo, visible, title, options, selected, apply, close};

    return <ListGroupingModal {...modal} />;
  }

  getRefName(count) {
    return 'count' + count.get('id');
  }

  renderCount(count) {
    return (
      <SectionsPane>
        <Section ref={this.getRefName(count)}>
          <SectionHeader callback={() => this.showGroupingModalFor(count)} count={count.get('count')}>
            {count.get('title')}
          </SectionHeader>

          <ul>
            {count.get('nested').map((nestedCount, index) => this.renderItem(count.get('id'), nestedCount, index))}
          </ul>
          {this.renderListGroupingModal(count)}
        </Section>
      </SectionsPane>
    );
  }

  render() {
    const { isLoaded, counts } = this.props;

    return (
      <NavFrame>
        <NavFrameHeaderContainer icon="icon-dp-streamline-bubble-conversation-4">Chat</NavFrameHeaderContainer>
        <NavFrameBody isLoaded={isLoaded}>
          {counts.map(count => this.renderCount(count))}
        </NavFrameBody>
      </NavFrame>
    );
  }
}
