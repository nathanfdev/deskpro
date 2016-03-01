import React, { PropTypes } from 'react';
import { Simple as Positioned } from 'DeskPRO/Component/Positioned/Simple';
import { ClickOut } from 'DeskPRO/Component/ClickOut';
import { AssignForm } from './AssignForm';
import { AssigneeAvatar } from './AssigneeAvatar';

export class AssignButton extends React.Component {

  static propTypes = {
    onSetEditing: PropTypes.func,
    onAssign: PropTypes.func.isRequired,
    task: PropTypes.object.isRequired
  };

  constructor(props) {
    super(props);

    this.state = {
      formOpened: false,
      task: props.task
    };
  }

  componentWillReceiveProps(nextProps) {
    this.setState({
      task: nextProps.task
    });
  }

  componentWillUnmount() {
    this.isUnmounted = true;
  }

  onOpenForm = () => {
    const { onSetEditing } = this.props;
    onSetEditing && onSetEditing(true);
    this.setState({
      formOpened: true
    });
  };

  onAssign = (assignee) => {
    this.setState({task: assignee});
    this.props.onAssign(assignee).then(this.closeForm);
  };

  closeForm = () => {
    const { onSetEditing } = this.props;
    onSetEditing && onSetEditing(false);
    if (this.isUnmounted) {
      return;
    }

    this.setState({
      formOpened: false
    });
  };

  hasAvatar() {
    const { task } = this.state;
    return task.get('agents').size || task.get('teams').size || task.get('departments').size;
  }

  render() {
    return (
      <div>
        <div className="dpwd--card-assigned" onClick={this.onOpenForm} ref="button">

          {this.hasAvatar()
            ? <AssigneeAvatar task={this.state.task}/>
            : <div className="dpw--avatar-face" style={{position: 'relative'}}>
            <i className="fa fa-caret-down"/>
          </div>
          }
        </div>

        <Positioned isOpen={this.state.formOpened}
                  positionTarget={this}
                  positionAt="right+5 top-10"
                  zIndex={1002}>

          <ClickOut onClickOut={this.closeForm} additionalNodes={[this.refs.button, 'assign-form']}>
            <AssignForm {...this.props} onSubmit={this.onAssign}/>
          </ClickOut>
        </Positioned>
      </div>
    );
  }
}
