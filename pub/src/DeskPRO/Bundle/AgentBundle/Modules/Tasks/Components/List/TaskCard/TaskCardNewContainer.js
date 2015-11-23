import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { listParamsNavSelector } from '../../../Selectors/list';
import { addTask } from '../../../Actions/listActions';
import moment from 'moment';

@connect(state => ({
  currentNav: listParamsNavSelector(state)
}))
export class TaskCardNewContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    updateData: PropTypes.object,
    currentNav: PropTypes.object,
    onClose: PropTypes.func.isRequired
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
    const { dispatch, onClose } = this.props;
    const submitData = {
      title: this.state.title,
      task_type: 'task',
      visibility: 'public',
      urgency: 1,
      display_order: 1
    };

    dispatch(addTask(submitData));
    onClose();
  };

  getDateDue() {
    const { updateData } = this.props;
    return updateData.date_due ? updateData.date_due : moment().endOf('day').format();
  }

  getProject() {
    const { updateData, currentNav } = this.props;
    const projects = currentNav.get('project');

    let project = null;
    if (projects) {
      project = projects.first();
    } else if (updateData.project) {
      project = updateData.project;
    }

    return project;
  }

  render() {
    const props = this.props;
    const { children } = props;
    const childProps = children.props;


    return React.cloneElement(children, {
      ...childProps,
      ...props,

      title: this.state.title,
      dateDue: this.getDateDue(),
      project: this.getProject(),

      onChangeTitle: this.onChangeTitle,
      onSaveTask: this.onSaveTask
    });
  }
}
