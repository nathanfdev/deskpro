import React, { PropTypes } from 'react';
import { Detached } from 'DeskPRO/Component/Positioned/Detached';
import { ClickOut } from 'DeskPRO/Component/ClickOut';
import { AssignForm } from './AssignForm';
import { AssigneeAvatar } from './AssigneeAvatar';

export class AssignButton extends React.Component {

  static propTypes = {
    onSetEditing: PropTypes.func,
    onAssign: PropTypes.func.isRequired,
    task: PropTypes.object
  };

  constructor(props) {
    super(props);

    this.state = {
      formOpened: false
    };
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
    const { task } = this.props;
    return task.get('agents').size || task.get('teams').size || task.get('departments').size;
  }

  render() {
    return (
      <div>
        <div className="dpwd--card-assigned" onClick={this.onOpenForm} ref="button">

          {this.hasAvatar()
            ? <AssigneeAvatar task={this.props.task}/>
            : <div className="dpw--avatar-face" style={{position: 'relative'}}>
            <i className="fa fa-caret-down"/>
          </div>
          }
        </div>

        <Detached isOpen={this.state.formOpened}
                  positionTarget={this}
                  positionAt="right+5 top-10"
                  zIndex={1002}>

          <ClickOut onClickOut={this.closeForm} additionalNodes={[this.refs.button, 'assign-form']}>
            <AssignForm {...this.props} onSubmit={this.onAssign}/>
          </ClickOut>
        </Detached>
      </div>
    );
  }
}
