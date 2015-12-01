import React, {Component, PropTypes} from 'react';
import { MassActionBar } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/MassActionBar/MassActionBar';
import { toggleMassAction }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Feedback/Actions/FeedbackListActions';

import { connect } from 'react-redux';
@connect(state => ({
  count: state.Feedback.list.get('selected').size
}))

export class MassActionContainer extends Component {

  static propTypes = {
    count: PropTypes.number
  };

  render() {
    const config = {
      checkbox: {
        count: this.props.count,
        action: toggleMassAction
      },
      actions: [
        { label: 'Status', options: [{ value: 1, label: 'something' }] },
        { label: 'Type', options: [{ value: 1, label: 'something' }]  },
        { label: 'Category', options: [{ value: 1, label: 'something' }]  },
        { icon: 'fa-asterisk', options: [{ value: 1, label: 'something' }]  }
      ]
    };


    return (
      <MassActionBar {...config} />
    );
  }
}