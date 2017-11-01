import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import { allSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';

@connect(state => ({
  tasks: allSelectorFactory('Task')(state)
}))
export class TaskCardPreviewContainer extends React.Component {

  static propTypes = {
    item: PropTypes.shape({
      id:    PropTypes.number.isRequired,
      width: PropTypes.number
    }),
    tasks:    PropTypes.object.isRequired,
    children: PropTypes.node.isRequired
  };

  render() {
    const props = this.props;
    const { tasks, item, children } = props;
    const childProps = children.props;

    return React.cloneElement(children, {
      ...childProps,

      task:  tasks.get(item.id),
      width: item.width
    });
  }
}
