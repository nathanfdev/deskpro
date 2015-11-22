import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { listParamsNavSelector } from '../../../Selectors/list';
import moment from 'moment';

@connect(state => ({
  currentNav: listParamsNavSelector(state)
}))
export class TaskCardNewContainer extends React.Component {

  static propTypes = {
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
    console.log('onSaveTask');
    this.props.onClose();
  };

  getDateDue() {
    const { updateData } = this.props;
    const date = updateData.date_due ? updateData.date_due : moment().endOf('day');

    return date.format();
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
      isValid: !!this.state.title,

      onChangeTitle: this.onChangeTitle,
      onSaveTask: this.onSaveTask
    });
  }
}
