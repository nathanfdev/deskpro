import React, { PropTypes } from 'react';
import { Detached } from 'DeskPRO/Component/Positioned/Detached';
import { ClickOut } from 'DeskPRO/Component/ClickOut';
import { AssignFormContainer } from './AssignForm/AssignFormContainer';
import { AssigneeContainer } from './AssigneeContainer';
import { AssigneeAvatar } from './AssigneeAvatar';

export class AssignButton extends React.Component {

  static propTypes = {
    onSetEditing: PropTypes.func.isRequired,
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
    this.props.onSetEditing(true);
    this.setState({
      formOpened: true
    });
  };

  onCloseForm = () => {
    this.props.onSetEditing(false);
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

  static renderButton() {
    return (
      <div className="dpw--avatar-face" style={{position: 'relative'}}>
        <i className="fa fa-caret-down" />
      </div>
    );
  }

  renderAvatar() {
    return (
      <AssigneeContainer>
        <AssigneeAvatar task={this.props.task} />
      </AssigneeContainer>
    );
  }

  render() {
    return (
      <div>
        <div className="dpwd--card-assigned"
             onClick={this.onOpenForm} ref="button">

          {this.hasAvatar() ? this.renderAvatar() : AssignButton.renderButton()}
        </div>

        <Detached isOpen={this.state.formOpened}
                  positionTarget={this}
                  positionAt="right+5 top-10"
                  zIndex={1002}>

          <ClickOut onClickOut={this.onCloseForm}
                    additionalNodes={[this.refs.button, 'assign-form']}>

            <AssignFormContainer {...this.props} onCloseForm={this.onCloseForm.bind(this)} />
          </ClickOut>
        </Detached>
      </div>
    );
  }
}
