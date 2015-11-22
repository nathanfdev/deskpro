import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { listParamsNavSelector } from '../../../Selectors/list';
import moment from 'moment';

@connect(state => ({
  currentNav: listParamsNavSelector(state)
}))
export class TaskCardNewContainer extends React.Component {

  static propTypes = {
    updateData: PropTypes.object
  };

  constructor(props) {
    super(props);
    this.state = {
      title: ''
    };
  }

  onChangeTitle = value => {
    this.setState({
      title: value
    });
  };

  onSaveTask = () => {
    console.log('onSaveTask');
  };

  getDateDue() {
    const { updateData } = this.props;
    const date = updateData.date_due ? updateData.date_due : moment().endOf('day');

    return date.format();
  }

  render() {
    const props = this.props;
    const { children } = props;
    const childProps = children.props;
    const projects = props.currentNav.get('project');

    return React.cloneElement(children, {
      ...childProps,
      ...props,

      title: this.state.title,
      dateDue: this.getDateDue(),
      project: projects ? projects.first() : null,

      onChangeTitle: this.onChangeTitle,
      onSaveTask: this.onSaveTask
    });
  }
}
