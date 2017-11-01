import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { immutableCountShape } from 'DeskPRO/Bundle/AppBundle/Shapes';
import { SectionsPane, Section, SectionHeader } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame';
import { NestedList } from './NestedList';
import { ListGroupingModalContainer } from './ListGroupingModalContainer'

export class ContentTab extends Component {
  static propTypes = {
    content: PropTypes.string.isRequired,
    count: immutableCountShape
  };

  constructor(props) {
    super(props);
    this.state = { isModalVisible: false };
  }

  toggleModal = () => {
    this.setState({ isModalVisible: !this.state.isModalVisible });
  };

  closeModal = () => {
    this.setState({ isModalVisible: false });
  };

  render() {
    const { content, count } = this.props;
    const label = content[0].toUpperCase() + content.slice(1);

    return (
      <SectionsPane>
        <Section>
          <SectionHeader label={label} ref="header" count={count.get('count')} callback={this.toggleModal} />
          <NestedList
            content={content}
            items={count.get('nested').toJS()}
            groupedBy={count.get('grouped_by')}
          />
          <ListGroupingModalContainer
            attachTo={this.refs.header}
            content={content}
            selected={count.get('grouped_by')}
            visible={this.state.isModalVisible}
            close={this.closeModal}
          />
        </Section>
      </SectionsPane>
    );
  }
}
