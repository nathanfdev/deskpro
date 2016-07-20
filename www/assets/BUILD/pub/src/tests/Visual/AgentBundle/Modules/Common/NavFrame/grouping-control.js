import React, { Component } from 'react';
import { storiesOf, action } from '@kadira/storybook';
import { css } from 'Visual/decorators';
import { ListGroupingModal, ListGroupingForm } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame';
import { formDemoProps, modalDemoProps } from 'DemoState/AgentBundle/Modules/Common/NavFrame/grouping-control';

/**
 * Demo component wrapping the ListGroupingModal together with its' DOM target
 */
class ListGroupingModalDemo extends Component {
  render = () =>
    <div>
      <span ref="target" />
      <ListGroupingModal {...this.props} attachTo={this.refs.target} />
    </div>
}

storiesOf('Common-Nav: List Grouping', module)
  .addDecorator(story => css(<div style={{padding: '20px'}}>{story()}</div>))
  .add('ListGroupingForm', () => <ListGroupingForm {...formDemoProps} />)
  .add('ListGroupingModal', () => <ListGroupingModalDemo {...modalDemoProps} />)
;
